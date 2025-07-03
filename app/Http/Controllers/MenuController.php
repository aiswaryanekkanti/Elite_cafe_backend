<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MenuInfo;
use App\Models\RestaurantTable;

class MenuController extends Controller
{
    public function menuinfo()
    {
        // Fetch non-deleted menu items
        $menuItems = MenuInfo::where('is_deleted', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $item->full_img_url = $item->img ? url($item->img) : null;
                return $item;
            });

        $items = $menuItems->groupBy('category');

        // Fetch online tables
        $onlineTables = RestaurantTable::where('table_type', 'offline')->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'online_tables' => $onlineTables, // 👈 Add this line
        ]);
    }
}
