<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MenuInfo;
 
class MenuController extends Controller
{
    public function menuinfo()
{
    // ✅ Correctly assign the filtered items
    $menuItems = MenuInfo::where('is_deleted', 0)->get();

    // Group by category
    $items = $menuItems->groupBy('category');
   
    return response()->json([
        'menuItems' => $menuItems,
        'items' => $items,
    ]);
}

}