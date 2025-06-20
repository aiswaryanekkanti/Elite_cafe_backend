<?php
namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
    'guest_name' => 'required|string|max:255',
    'guest_email' => 'required|email|max:255',
    'guest_phone' => 'required|string|max:20',
    'count' => 'required|string|',
    'reservation_date' => 'required|date|after_or_equal:today',
    'time_slot' => 'required|string',
    'requests' => 'string|max:200',
]);

  $record = Reservation::create([
        'guest_name' => $validated['guest_name'],
        'guest_email' => $validated['guest_email'],
        'guest_phone' => $validated['guest_phone'],
        'count' => $validated['count'],
        'reservation_date' => $validated['reservation_date'],
        'time_slot' => $validated['time_slot'],
        'requests' => $validated['requests'] ?? '',
    ]);


    return response()->json([
        'success' => true,
        'message' => 'Reservation saved successfully.',
        'data' => $reservation,
    ], 201);

        
}

}

