<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    // ✅ List all reservations
    public function index(Request $request)
{
    $query = Reservation::with('tables');
    $today = now()->toDateString();

    // Handle filter
    if ($request->filter === 'today') {
        $query->whereDate('reservation_date', $today);
    } elseif ($request->filter === 'upcoming') {
        $query->whereDate('reservation_date', '>', $today);
    } elseif ($request->filter === 'previous') {
        $query->whereDate('reservation_date', '<', $today);

        // Optional sorting
        $sort = $request->get('sort_date', 'desc');
        $query->orderBy('reservation_date', $sort);
    } else {
        // Default to today if filter is unknown
        $query->whereDate('reservation_date', $today);
    }

    // Handle search
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('reference_no', 'like', "%{$search}%");
        });
    }

    $reservations = $query->orderBy('reservation_date', 'desc')->get();

    return response()->json($reservations);
}



    // ✅ Show a specific reservation
    public function show($id)
    {
        $reservation = Reservation::with('tables')->find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        return response()->json($reservation);
    }

    // ✅ Cancel a reservation
    public function cancel($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }

    // ✅ Restore a cancelled reservation
    public function restore($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reservation not found'], 404);
        }

        $reservation->status = 'confirmed';
        $reservation->save();

        return response()->json(['message' => 'Reservation restored successfully']);
    }

    // ✅ Show only cancelled reservations
    public function cancelled()
    {
        $cancelled = Reservation::with('tables')
            ->where('status', 'cancelled')
            ->latest()
            ->get();

        return response()->json($cancelled);
    }
}
