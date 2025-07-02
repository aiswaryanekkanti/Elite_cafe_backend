<?php
// app/Http/Controllers/AdminOrderController.php

namespace App\Http\Controllers;

use App\Models\CustOrderInfo;
use Illuminate\Http\Request;
use Carbon\Carbon;  // <-- correct import here

class AdminOrderController extends Controller
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
                // No filter applied
                break;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json($orders);
    }
}
