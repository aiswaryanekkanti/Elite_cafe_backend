<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Staff;
use App\Models\StaffTableAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReservationController extends Controller
{
    /**
     * Show today's or week's reservations
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 1); // 1 = today, up to 7

        $from = Carbon::today();
        $to = Carbon::today()->addDays(min(7, $days));

        $reservations = Reservation::with('tables')
            ->whereBetween('reservation_date', [$from, $to])
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        return response()->json([
            'status' => 'success',
            'reservations' => $reservations
        ]);
    }

    /**
     * Store a new reservation (manual or auto)
     */
    public function store(Request $request)
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
            'manual_table_ids'  => 'nullable|array',
            'manual_table_ids.*' => 'integer|exists:tables,id',
        ]);

        $startDateTime = Carbon::parse($validated['reservation_date'] . ' ' . $validated['reservation_time']);
        $endDateTime = $startDateTime->copy()->addMinutes(60);

        DB::beginTransaction();

        try {
            $tables = [];

            if (!empty($validated['manual_table_ids'])) {
                $tables = RestaurantTable::whereIn('id', $validated['manual_table_ids'])->get();
                foreach ($tables as $table) {
                    $isBooked = Reservation::whereHas('tables', function ($q) use ($table) {
                        $q->where('restaurant_table_id', $table->id);
                    })->where(function ($q) use ($startDateTime, $endDateTime) {
                        $q->where('reserved_from', '<', $endDateTime)
                          ->where('reserved_to', '>', $startDateTime);
                    })->exists();
                    if ($isBooked) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "Table {$table->name} is already reserved.",
                        ], 400);
                    }
                }
            } else {
                $tables = $this->assignTables($validated['persons'], $startDateTime, $endDateTime);
                if (empty($tables)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No available tables for this time.',
                    ], 400);
                }
            }

            $reservation = Reservation::create([
                'first_name'        => $validated['first_name'],
                'last_name'         => $validated['last_name'],
                'email'             => $validated['email'],
                'phone_number'      => $validated['phone_number'],
                'booked_persons'    => $validated['persons'],
                'reservation_date'  => $validated['reservation_date'],
                'reservation_time'  => $startDateTime->format('H:i:s'),
                'reserved_from'     => $startDateTime,
                'reserved_to'       => $endDateTime,
                'status'            => 'confirmed',
                'message'           => $validated['message'] ?? null,
            ]);

            $reservation->tables()->attach(collect($tables)->pluck('id'));

            // Assign staff
            foreach ($tables as $table) {
                $assigned = [];

                foreach (['Waiter', 'Manager', 'Cleaner'] as $role) {
                    $roleColumn = strtolower($role) . '_id';

                    $staff = Staff::where('role', $role)
                        ->whereNotIn('staff_id', function ($query) use ($roleColumn, $validated, $startDateTime) {
                            $query->select($roleColumn)
                                ->from('staff_table_assignments')
                                ->where('assignment_date', $validated['reservation_date'])
                                ->where('assignment_time', $startDateTime->format('H:i:s'))
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
                        'assignment_date' => $validated['reservation_date'],
                        'assignment_time' => $startDateTime->format('H:i:s'),
                        'waiter_id'       => $assigned['Waiter']->staff_id,
                        'manager_id'      => $assigned['Manager']->staff_id,
                        'cleaner_id'      => $assigned['Cleaner']->staff_id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Reservation created successfully.',
                'reservation' => $reservation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reservation
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // For simplicity, only updating user data and message
        $validated = $request->validate([
            'first_name' => 'string|max:200',
            'last_name' => 'string|max:300',
            'email' => 'email|max:200',
            'phone_number' => 'digits:10',
            'message' => 'nullable|string|max:300',
        ]);

        $reservation->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation updated.',
            'reservation' => $reservation
        ]);
    }

    /**
     * Cancel a reservation
     */
    public function cancel($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'cancelled']);

        return response()->json([
            'status' => 'success',
            'message' => 'Reservation cancelled.',
        ]);
        $reservation->tables()->detach();

    }

    /**
     * Find table combination
     */
    private function assignTables($persons, Carbon $start, Carbon $end): array
    {
        $tables = RestaurantTable::orderBy('capacity')->get();
        $best = [];
        $min = PHP_INT_MAX;

        $try = function ($i, $comb, $cap) use (&$tables, &$best, &$min, $persons, $start, $end, &$try) {
            if ($cap >= $persons && $cap < $min) {
                $allAvailable = true;
                foreach ($comb as $t) {
                    $booked = Reservation::whereHas('tables', function ($q) use ($t) {
                        $q->where('restaurant_table_id', $t->id);
                    })->where(function ($q) use ($start, $end) {
                        $q->where('reserved_from', '<', $end)->where('reserved_to', '>', $start);
                    })->exists();

                    if ($booked) {
                        $allAvailable = false;
                        break;
                    }
                }
                if ($allAvailable) {
                    $best = $comb;
                    $min = $cap;
                }
                return;
            }

            if ($i >= count($tables)) return;

            $try($i + 1, array_merge($comb, [$tables[$i]]), $cap + $tables[$i]->capacity);
            $try($i + 1, $comb, $cap);
        };

        $try(0, [], 0);

        return $best;
    }
}
