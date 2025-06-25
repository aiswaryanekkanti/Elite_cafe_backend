<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MenuInfo;
use App\Models\CustomerDetails;
 
class OrderController extends Controller
{
    public function orderinfo()
    {
     
        $menuItems = MenuInfo::all();
 
 
        $items = $menuItems->groupBy('category');
       
        return response()->json([
            
            'menuItems' => $menuItems,
            'items' => $items,
            
        ]);
    }
    // app/Models/Order.php
public function customer()
{
    return $this->belongsTo(CustomerDetail::class, 'customer_email', 'email');
}

}
 