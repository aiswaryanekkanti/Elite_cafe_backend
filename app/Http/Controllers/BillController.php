<?php

namespace App\Http\Controllers;

use App\Models\CustOrderInfo;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BillController extends Controller
{
    public function index(Request $request)
{
    $query = CustOrderInfo::query();

    $dateFilter = $request->query('date_filter');

    switch ($dateFilter) {
        case 'today':
            $query->whereDate('created_at', Carbon::today());
            break;

        case 'this_week':
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            break;

        case 'this_month':
            $query->whereYear('created_at', Carbon::now()->year)
                  ->whereMonth('created_at', Carbon::now()->month);
            break;

        case 'last_month':
            $lastMonth = Carbon::now()->subMonth();
            $query->whereYear('created_at', $lastMonth->year)
                  ->whereMonth('created_at', $lastMonth->month);
            break;

        case 'last_3_months':
            $query->where('created_at', '>=', Carbon::now()->subMonths(3));
            break;

        default:
            // No filter
            break;
    }

    // Select only necessary fields
    $orders = $query->orderBy('created_at', 'desc')
                    ->select('id', 'total', 'created_at') // Include created_at if needed
                    ->get();

    return response()->json($orders);
}

      
    public function show($id)
{
    $order = CustOrderInfo::findOrFail($id);

    $cartItems = json_decode($order->cart, true) ?? [];

    return response()->json([
        'order' => [
            'id' => $order->id,
            'name' => $order->name,
            'phone' => $order->phone,
            'type' => $order->type,
            'total' => $order->total,
            'created_at' => $order->created_at,
        ],
        'items' => $cartItems,
    ]);
}

}
