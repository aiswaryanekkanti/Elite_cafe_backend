<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyOrders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        $monthlyRevenue = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        $topMenuItem = DB::table('order_items')
            ->select('menu_item_name', DB::raw('COUNT(*) as count'))
            ->groupBy('menu_item_name')
            ->orderByDesc('count')
            ->limit(1)
            ->value('menu_item_name');

        $newCustomers = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

        return view('admin.dashboard', [
            'monthlyOrders' => $monthlyOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'topMenuItem' => $topMenuItem ?? 'N/A',
            'newCustomers' => $newCustomers,
        ]);
    }
}
