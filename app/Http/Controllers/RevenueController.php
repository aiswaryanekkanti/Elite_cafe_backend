<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;

class RevenueController extends Controller
{
    public function index()
    {
        $orders = Order::all();

        $now = Carbon::now();

        $revenueDay = 0;
        $revenueWeek = 0;
        $revenueMonth = 0;
        $revenueYear = 0;

        foreach ($orders as $order) {
            $created = Carbon::parse($order->created_at);
            $total = $order->total ?? 0;

            if ($created->isSameDay($now)) {
                $revenueDay += $total;
            }
            if ($created->isSameWeek($now)) {
                $revenueWeek += $total;
            }
            if ($created->isSameMonth($now)) {
                $revenueMonth += $total;
            }
            if ($created->isSameYear($now)) {
                $revenueYear += $total;
            }
        }

        // Return JSON response for API
        return response()->json([
            'revenueDay' => $revenueDay,
            'revenueWeek' => $revenueWeek,
            'revenueMonth' => $revenueMonth,
            'revenueYear' => $revenueYear,
        ]);
    }
}
