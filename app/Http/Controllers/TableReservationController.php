<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableInfo;
use App\Models\CountInfo;

class TableReservationController extends Controller
{
    public function reservationdetails(Request $request)
{
    // ✅ Validate incoming request
    $validated = $request->validate([
        'first_name'        => 'required|string|max:200',
        'last_name'         => 'required|string|max:300',
        'email'             => 'required|email|max:200',
        'phone_number'      => 'required|digits:10',
        'persons'           => 'required|string|max:10', // e.g. '1-4', '5-8'
        'reservation_date'  => 'required|date',
        'reservation_time'  => 'required|date_format:H:i',
        'message'           => 'nullable|string|max:300',
    ]);
 
    // 🔧 Format reservation_time to MySQL TIME (H:i:s)
    $validated['reservation_time'] = date("H:i:s", strtotime($validated['reservation_time']));
    $reservationDate = $validated['reservation_date'];
    $reservationTime = $validated['reservation_time'];
    $persons = $validated['persons'];
 
    // 🔍 Check if reservation is for today
    $isToday = date('Y-m-d') === $reservationDate;
 
    if ($isToday) {
        // ✅ For today: check availability from table_reservation_count
        $available = CountInfo::where('seats', $persons)
            ->where('time_slot', $reservationTime)
            ->where('count', '>', 0)
            ->first();
 
        if (!$available) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, this time slot is fully booked.',
            ], 400);
        }
    } else {
        // ✅ For future date: check count from table_reservation directly
        $existing = TableInfo::where('reservation_date', $reservationDate)
            ->where('reservation_time', $reservationTime)
            ->where('persons', $persons)
            ->count();
 
        if ($existing >= 3) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, this time slot is fully booked for that date.',
            ], 400);
        }
    }
}

}