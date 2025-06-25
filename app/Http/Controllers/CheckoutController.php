<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

// class CheckoutController extends Controller
// {
//     public function dineIn(Request $request)
//     {
//         // Validate and create order
//         $order = Order::create([
//             'user_id' => $request->user()->id,
//             'type' => 'dinein',
//             'status' => 'pending',
//             // ...other fields
//         ]);
//         return response()->json(['success' => true, 'order_id' => $order->id]);
//     }

//     public function takeaway(Request $request)
//     {
//         $order = Order::create([
//             'user_id' => $request->user()->id,
//             'type' => 'takeaway',
//             'status' => 'pending',
//             // ...other fields
//         ]);
//         return response()->json(['success' => true, 'order_id' => $order->id]);
//     }

//     public function delivery(Request $request)
//     {
//         $validated = $request->validate([
//             'name' => 'required|string|max:255',
//             'phone' => 'required|digits:10',
//             'pincode' => 'required|digits:6',
//             'locality' => 'required|string|max:255',
//             'address' => 'required|string|max:1000',
//             'payment_method' => 'required|in:online,cod',
//         ]);

//         $order = Order::create([
//             'user_id' => $request->user()->id,
//             'type' => 'delivery',
//             'status' => 'pending',
//             'name' => $validated['name'],
//             'phone' => $validated['phone'],
//             'pincode' => $validated['pincode'],
//             'locality' => $validated['locality'],
//             'address' => $validated['address'],
//             'payment_method' => $validated['payment_method'],
//             // ...other fields
//         ]);
//         return response()->json(['success' => true, 'order_id' => $order->id]);
//     }
//     use App\Models\MenuItemStock;

// public function updateStockAfterOrder($menuItemId, $qtyOrdered)
// {
//     $stock = MenuItemStock::where('menu_item_id', $menuItemId)->first();
//     if ($stock) {
//         $stock->quantity -= $qtyOrdered;
//         if ($stock->quantity <= 0) {
//             $stock->quantity = 0;
//             $stock->is_available = 'no';
//         }
//         $stock->save();
//     }
// }

// }
