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
 
class ReservationController extends Controller
{
    public function reservationdetails(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:200',
            'last_name'         => 'required|string|max:300',
            'email'             => 'required|email|max:200',
            'phone_number'      => 'required|digits:10',
            'persons'           => 'required|integer|min:1|max:100',
            'reservation_date'  => 'required|date_format:Y-m-d|after_or_equal:today',
            'reservation_time'  => 'required|date_format:H:i',
            'message'           => 'nullable|string|max:300',
        ]);
 
        $bookedPersons   = $validated['persons'];
        $reservationDate = $validated['reservation_date'];
        $reservationTime = $validated['reservation_time'];
        $slotDurationMinutes = 60;
 
        $startDateTime = Carbon::parse($reservationDate . ' ' . $reservationTime);
        $endDateTime   = $startDateTime->copy()->addMinutes($slotDurationMinutes);
 
        if ($startDateTime->isToday() && $startDateTime->lt(Carbon::now())) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cannot book for a past time today.',
            ], 400);
        }
 
        $assignedTables = $this->findTableCombinations($bookedPersons, $startDateTime, $endDateTime);
 
        if (empty($assignedTables)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, no available tables for ' . $bookedPersons . ' guests at ' .
                            $startDateTime->format('h:i A') . ' on ' . $reservationDate . '.',
            ], 400);
        }
 
        DB::beginTransaction();
 
        try {
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
                'status'            => 'confirmed',
                'message'           => $validated['message'],
            ]);
 
            $reservation->tables()->attach(collect($assignedTables)->pluck('id'));
 
            foreach ($assignedTables as $table) {
                $assigned = [];
 
                foreach (['Waiter', 'Manager', 'Cleaner'] as $role) {
                    $roleColumn = strtolower($role) . '_id';
 
                    $staff = Staff::where('role', $role)
                        ->whereNotIn('staff_id', function ($query) use ($roleColumn, $reservationDate, $reservationTime) {
                            $query->select($roleColumn)
                                ->from('staff_table_assignments')
                                ->where('assignment_date', $reservationDate)
                                ->where('assignment_time', $reservationTime)
                                ->groupBy($roleColumn)
                                ->havingRaw('COUNT(*) >= 3');
                        })
                        ->inRandomOrder()
                        ->first();
 
                    $assigned[$role] = $staff;
                }
 
                if ($assigned['Waiter'] && $assigned['Manager'] && $assigned['Cleaner']) {
                    StaffTableAssignment::create([
                        'table_id'        => $table->id,
                        'assignment_date' => $reservationDate,
                        'assignment_time' => $reservationTime,
                        'waiter_id'       => $assigned['Waiter']->staff_id,
                        'manager_id'      => $assigned['Manager']->staff_id,
                        'cleaner_id'      => $assigned['Cleaner']->staff_id,
                    ]);
                }
            }
 
            DB::commit();
 
            $assignedTableNames = collect($assignedTables)->pluck('name')->implode(', ');
            $totalCapacity = collect($assignedTables)->sum('capacity');
 
            $assignedTableIds = collect($assignedTables)->pluck('id');
            $staffAssignments = StaffTableAssignment::with(['waiter', 'manager', 'cleaner'])
                ->whereIn('table_id', $assignedTableIds)
                ->where('assignment_date', $reservationDate)
                ->where('assignment_time', $reservationTime)
                ->get();
 
            $staffDetails = [];
 
            foreach ($staffAssignments as $assignment) {
                foreach (['waiter', 'manager', 'cleaner'] as $role) {
                    $staffMember = $assignment->$role;
                    if ($staffMember) {
                        $staffDetails[$staffMember->staff_id]['staff_id'] = $staffMember->staff_id;
                        $staffDetails[$staffMember->staff_id]['name'] = $staffMember->first_name . ' ' . $staffMember->last_name;
                        $staffDetails[$staffMember->staff_id]['role'] = $staffMember->role;
                        $staffDetails[$staffMember->staff_id]['assigned_tables_ids'][] = $assignment->table_id;
                    }
                }
            }
 
            $groupedStaffDetails = array_values(array_map(function ($s) {
                $s['assigned_tables_ids'] = array_values(array_unique($s['assigned_tables_ids']));
                return $s;
            }, $staffDetails));
 
            return response()->json([
                'status'                  => 'success',
                'message'                 => 'Reservation successfully created for ' . $bookedPersons . ' guests.',
                'data'                    => $reservation,
                'assigned_tables'         => collect($assignedTables)->map(function ($table) {
                    return ['id' => $table->id, 'name' => $table->name, 'capacity' => $table->capacity];
                }),
                'assigned_table_names'    => $assignedTableNames,
                'total_assigned_capacity' => $totalCapacity,
                'assigned_staff'          => $groupedStaffDetails,
            ], 201);
 
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reservation failed: ' . $e->getMessage(), ['exception' => $e]);
 
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error. Please try again.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
 
    protected function findTableCombinations(int $targetPersons, Carbon $startDateTime, Carbon $endDateTime): array
    {
        // Limit the number of tables available for online reservations
        $capacityLimits = [
            2 => 3, // Only 3 of 2-seaters available online
            4 => 6, // Only 6 of 4-seaters available online
        ];
 
        $allTables = collect();
 
        foreach ($capacityLimits as $capacity => $limit) {
            $tables = RestaurantTable::where('capacity', $capacity)
                ->orderBy('id')
                ->take($limit)
                ->get();
 
            $allTables = $allTables->merge($tables);
        }
 
        // Sort by capacity ascending
        $allTables = $allTables->sortBy('capacity')->values();
 
        $bestCombination = [];
        $minTotalCapacity = PHP_INT_MAX;
        $minTableCount = PHP_INT_MAX;
 
        $find = function ($index, $currentCapacity, $currentCombination) use (
            &$find, $allTables, $targetPersons, $startDateTime, $endDateTime,
            &$bestCombination, &$minTotalCapacity, &$minTableCount
        ) {
            if ($currentCapacity >= $minTotalCapacity && count($currentCombination) >= $minTableCount) {
                return;
            }
 
            if ($currentCapacity >= $targetPersons) {
                $allAvailable = true;
                foreach ($currentCombination as $table) {
                    $isBooked = Reservation::whereHas('tables', function ($q) use ($table) {
                        $q->where('restaurant_table_id', $table->id);
                    })->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q->where('reserved_from', '<', $endDateTime)
                          ->where('reserved_to', '>', $startDateTime);
                    })->exists();
 
                    if ($isBooked) {
                        $allAvailable = false;
                        break;
                    }
                }
 
                if ($allAvailable) {
                    $bestCombination = $currentCombination;
                    $minTotalCapacity = $currentCapacity;
                    $minTableCount = count($currentCombination);
                }
                return;
            }
 
            if ($index >= $allTables->count()) return;
 
            $table = $allTables[$index];
 
            if (count($currentCombination) < 4) {
                $find($index + 1, $currentCapacity + $table->capacity, array_merge($currentCombination, [$table]));
            }
 
            $find($index + 1, $currentCapacity, $currentCombination);
        };
 
        $find(0, 0, []);
 
        return $bestCombination;
    }
}
 
 