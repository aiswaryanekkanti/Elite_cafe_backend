<?php
// app/Http/Controllers/AdminOrderController.php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
 public function index()
 {
 $orders = Order::orderBy('created_at', 'desc')->get();
return response()->json($orders);
}
}
