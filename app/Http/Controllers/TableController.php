<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Staff;
use App\Models\StaffTableAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    // Define constants for better maintainability and readability
    const RESERVATION_SLOT_DURATION_MINUTES = 60; // Duration of a single reservation slot in minutes
    const MAX_STAFF_ASSIGNMENTS_PER_SLOT = 3;     // Max tables a staff member can be assigned to per slot
    const MAX_TABLES_IN_COMBO = 4;                // Maximum number of tables allowed in a single combination for a reservation

    /**
     * Handles the reservation details submission, finds available tables,
     * creates a reservation, and assigns staff.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reservationdetails(Request $request)
    {
        // 1. Validate incoming request data
        $validated = $request->validate([
            'first_name'        => 'required|string|max:200',
            'last_name'         => 'required|string|max:300',
            'email'             => 'required|email|max:200',
            'phone_number'      => 'required|digits:10',
            'persons'           => 'required|string|max:10', // Changed to string as it's cast to int later
            'reservation_date'  => 'required|date',
            'reservation_time'  => 'required|date_format:H:i',
            'message'           => 'nullable|string|max:300',
        ]);

        // 2. Prepare reservation details
        $bookedPersons = (int)$validated['persons'];
        $reservationDate = $validated['reservation_date'];
        $reservationTime = $validated['reservation_time'];

        // Calculate reservation start and end times based on the slot duration
        $startDateTime = Carbon::parse($reservationDate . ' ' . $reservationTime);
        $endDateTime = $startDateTime->copy()->addMinutes(self::RESERVATION_SLOT_DURATION_MINUTES);

        // 3. Prevent booking for past times on the current day
        if ($startDateTime->isToday() && $startDateTime->lt(Carbon::now())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot book for a past time today. Please select a future time.',
            ], 400);
        }

        // 4. Find available table combinations
        // 4. Find available table combinations
$assignedTables = $this->findTableCombinations($bookedPersons, $startDateTime, $endDateTime);

// If no tables are found, return an error response
if (empty($assignedTables)) {
    return response()->json([
        'status' => 'error',
        'message' => 'Sorry, no available online tables for ' . $bookedPersons . ' guests at ' .
                     $startDateTime->format('h:i A') . ' on ' . $reservationDate . '. Please try a different time or date.',
    ], 400);
}


        // 5. Start database transaction for atomicity
        DB::beginTransaction();

        try {
            // 6. Create the new reservation record
            $reservation = Reservation::create([
                'first_name'        => $validated['first_name'],
                'last_name'         => $validated['last_name'],
                'email'             => $validated['email'],
                'phone_number'      => $validated['phone_number'],
                'booked_persons'    => $bookedPersons,
                'reservation_date'  => $reservationDate,
                'reservation_time'  => $startDateTime->format('H:i:s'),
                'reserved_from'     => $startDateTime,
                'reserved_to'       => $endDateTime,
                'status'            => 'confirmed', // Default status
                'message'           => $validated['message'],
            ]);

            // 7. Attach the assigned tables to the reservation
            // Using pluck('id') to get an array of table IDs for the many-to-many relationship
            $reservation->tables()->attach(collect($assignedTables)->pluck('id'));

            // 8. Assign staff to each reserved table
            foreach ($assignedTables as $table) {
                $assignedStaffForTable = []; // To hold the assigned staff for the current table

                // Iterate through required staff roles
                foreach (['Waiter', 'Manager', 'Cleaner'] as $role) {
                    $roleColumn = strtolower($role) . '_id'; // e.g., 'waiter_id'

                    // Find a staff member for the current role who is not assigned to too many tables
                    $staff = Staff::where('role', $role)
                        ->whereNotIn('staff_id', function ($query) use ($roleColumn, $reservationDate, $reservationTime) {
                            $query->select($roleColumn)
                                ->from('staff_table_assignments')
                                ->where('assignment_date', $reservationDate)
                                ->where('assignment_time', $reservationTime)
                                ->groupBy($roleColumn)
                                ->havingRaw('COUNT(*) >= ' . self::MAX_STAFF_ASSIGNMENTS_PER_SLOT);
                        })
                        ->inRandomOrder() // Pick a random available staff member
                        ->first();

                    $assignedStaffForTable[$role] = $staff;
                }

                // Only create a staff assignment record if all three roles are successfully found
                // Note: If a staff member for a role isn't found, this table's assignment will be skipped.
                // Consider business logic: should the reservation fail if staff can't be fully assigned?
                if ($assignedStaffForTable['Waiter'] && $assignedStaffForTable['Manager'] && $assignedStaffForTable['Cleaner']) {
                    StaffTableAssignment::create([
                        'table_id'          => $table->id,
                        'assignment_date'   => $reservationDate,
                        'assignment_time'   => $reservationTime,
                        'waiter_id'         => $assignedStaffForTable['Waiter']->staff_id,
                        'manager_id'        => $assignedStaffForTable['Manager']->staff_id,
                        'cleaner_id'        => $assignedStaffForTable['Cleaner']->staff_id,
                    ]);
                } else {
                    Log::warning("Could not assign all staff roles for table ID: {$table->id} at {$reservationDate} {$reservationTime}.");
                }
            }

            // 9. Commit the transaction if all operations are successful
            DB::commit();

            // 10. Prepare response data
            $assignedTableNames = collect($assignedTables)->pluck('name')->implode(', ');
            $totalCapacity = collect($assignedTables)->sum('capacity');
            $assignedTableIds = collect($assignedTables)->pluck('id');

            // Fetch staff assignments with their details for the response
            $staffAssignments = StaffTableAssignment::with(['waiter', 'manager', 'cleaner'])
                ->whereIn('table_id', $assignedTableIds)
                ->where('assignment_date', $reservationDate)
                ->where('assignment_time', $reservationTime)
                ->get();

            // Group staff details to avoid duplicates and show assigned tables
            $staffDetails = [];
            foreach ($staffAssignments as $assignment) {
                foreach (['waiter', 'manager', 'cleaner'] as $role) {
                    $staffMember = $assignment->$role;
                    if ($staffMember) {
                        $staffDetails[$staffMember->staff_id]['staff_id'] = $staffMember->staff_id;
                        $staffDetails[$staffMember->staff_id]['name'] = $staffMember->first_name . ' ' . $staffMember->last_name;
                        $staffDetails[$staffMember->staff_id]['role'] = $staffMember->role;
                        // Ensure assigned_tables_ids is an array before pushing
                        if (!isset($staffDetails[$staffMember->staff_id]['assigned_tables_ids'])) {
                            $staffDetails[$staffMember->staff_id]['assigned_tables_ids'] = [];
                        }
                        $staffDetails[$staffMember->staff_id]['assigned_tables_ids'][] = $assignment->table_id;
                    }
                }
            }

            // Convert associative array to indexed array and ensure unique table IDs
            $groupedStaffDetails = array_values(array_map(function ($s) {
                $s['assigned_tables_ids'] = array_values(array_unique($s['assigned_tables_ids']));
                return $s;
            }, $staffDetails));

            // 11. Return success response
            return response()->json([
                'status'                    => 'success',
                'message'                   => 'Reservation successfully created for ' . $bookedPersons . ' guests.',
                'data'                      => $reservation,
                'assigned_tables'           => collect($assignedTables)->map(function ($table) {
                    return ['id' => $table->id, 'name' => $table->name, 'capacity' => $table->capacity];
                }),
                'assigned_table_names'      => $assignedTableNames,
                'total_assigned_capacity'   => $totalCapacity,
                'assigned_staff'            => $groupedStaffDetails,
            ], 201);

        } catch (\Exception $e) {
            // 12. Rollback transaction on error and log the exception
            DB::rollBack();
            Log::error('Reservation failed: ' . $e->getMessage(), ['exception' => $e, 'request' => $request->all()]);

            // Return error response
            return response()->json([
                'status' => 'error',
                'message' => 'A server error occurred during reservation. Please try again or contact support.',
                'error' => $e->getMessage() // Include error message for debugging in development
            ], 500);
        }
    }

    /**
     * Finds the best combination of available 'online' tables for a given number of persons
     * and reservation time slot.
     *
     * Prioritizes a single table if exact fit, then combinations with minimal excess capacity
     * and fewest tables.
     *
     * @param int $targetPersons The number of guests for the reservation.
     * @param Carbon $startDateTime The start time of the reservation.
     * @param Carbon $endDateTime The end time of the reservation.
     * @return array An array of RestaurantTable models representing the assigned tables.
     */
    protected function findTableCombinations(int $targetPersons, Carbon $startDateTime, Carbon $endDateTime): array
{
    // Fetch all 'online' tables, ordered by capacity for efficient searching
    $allTables = RestaurantTable::where('table_type', 'online')->orderBy('capacity')->get();

    // Separate 2-seaters and 4-seaters for specific logic
    $twoSeaters = $allTables->where('capacity', 2)->values();
    $fourSeaters = $allTables->where('capacity', 4)->values();

    // Reusable closure to check if a table is available for the given time slot
    $isAvailable = function ($table) use ($startDateTime, $endDateTime) {
        return !Reservation::whereHas('tables', function ($q) use ($table) {
            $q->where('restaurant_table_id', $table->id);
        })
        ->where(function ($q) use ($startDateTime, $endDateTime) {
            // Check for overlapping reservations:
            // Reservation starts before our end time AND
            // Reservation ends after our start time
            $q->where('reserved_from', '<', $endDateTime)
              ->where('reserved_to', '>', $startDateTime);
        })
        ->exists();
    };

    // Case 1: Exactly 2 guests - try to find a single 2-seater
    if ($targetPersons === 2) {
        foreach ($twoSeaters as $table) {
            if ($isAvailable($table)) {
                return [$table]; // Found an available 2-seater
            }
        }
        return []; // No 2-seater available
    }

    // Case 2: Exactly 4 guests - prefer a single 4-seater, fallback to two 2-seaters
    elseif ($targetPersons === 4) {
        foreach ($fourSeaters as $table) {
            if ($isAvailable($table)) {
                return [$table]; // Found an available 4-seater
            }
        }

        // If no 4-seater, try to combine two 2-seaters
        $availableTwos = $twoSeaters->filter($isAvailable)->values();
        if ($availableTwos->count() >= 2) {
            return [$availableTwos[0], $availableTwos[1]]; // Use the first two available 2-seaters
        }

        return []; // No 4-seater or two 2-seaters available
    }

    // Case 3: For all other guest counts (3, 5, 6, 7+, etc.)
    else {
        // Filter all tables to get only the ones currently available
        $availableTables = $allTables->filter($isAvailable)->values();

        $bestCombo = [];
        $minCapacity = PHP_INT_MAX;
        $minTableCount = PHP_INT_MAX;

        $totalTables = $availableTables->count();

        // Recursive function to find the optimal table combination
        $findCombo = function (
            int $index,
            array $currCombo,
            int $currCapacity
        ) use (
            &$findCombo, $availableTables, $targetPersons,
            &$bestCombo, &$minCapacity, &$minTableCount, $totalTables
        ) {
            // Base Case 1: If current capacity meets or exceeds target persons
            if ($currCapacity >= $targetPersons) {
                // Check if this combination is better than the current best
                if (
                    $currCapacity < $minCapacity ||
                    ($currCapacity === $minCapacity && count($currCombo) < $minTableCount)
                ) {
                    $bestCombo = $currCombo;
                    $minCapacity = $currCapacity;
                    $minTableCount = count($currCombo);
                }
                return; // Stop exploring this path
            }

            // Base Case 2: If no more tables to consider OR combo has too many tables
            if ($index >= $totalTables || count($currCombo) >= self::MAX_TABLES_IN_COMBO) {
                return;
            }

            // Recursive Step 1: Include the current table
            $findCombo(
                $index + 1,
                array_merge($currCombo, [$availableTables[$index]]),
                $currCapacity + $availableTables[$index]->capacity
            );

            // Recursive Step 2: Exclude the current table
            $findCombo(
                $index + 1,
                $currCombo,
                $currCapacity
            );
        };

        // Start the recursive search from the first available table with an empty combination
        $findCombo(0, [], 0);

        return $bestCombo; // Return the best combination found
    }
}

}
