<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MenuInfo;
 
class MenuController extends Controller
{
    public function menuinfo()
{
    $menuItems = MenuInfo::where('is_deleted', 0)
    //new
        ->orderBy('category')
        ->orderBy('name')
        ->get()
        ->map(function ($item) {
            $item->full_img_url = $item->img ? url($item->img) : null;
            return $item;
        });
//new
    $items = $menuItems->groupBy('category');

    return response()->json([
        'status' => 'success',
        'data' => $items
    ]);
}


}