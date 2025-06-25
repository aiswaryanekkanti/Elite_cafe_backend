<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutApiController extends Controller
{
    public function placeOrder(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'payment_method' => 'required|in:cod,online',
            'online_gateway' => 'nullable|required_if:payment_method,online|in:razorpay,stripe',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // You may fetch cart data from the frontend (e.g., send it in request)
        $cart = $request->input('cart', []);

        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Calculate totals
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $tax = $subtotal * 0.05;
        $deliveryCharge = 50;
        $discount = $request->input('discount', 0); // frontend can send this
        $finalTotal = $subtotal + $tax + $deliveryCharge - $discount;

        // Create Order
        $order = Order::create([
            'user_id'        => $user->id,
            'address_line1'  => $request->address,
            'payment_method' => $request->payment_method,
            'status'         => $request->payment_method === 'cod' ? 'pending' : 'initiated',
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'delivery_charge'=> $deliveryCharge,
            'discount'       => $discount,
            'total_amount'   => $finalTotal,
        ]);

        // Store order items
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        // Return response
        if ($request->payment_method === 'online') {
            // Later: add integration with Razorpay/Stripe and return redirect URL
            return response()->json([
                'success' => true,
                'redirect_url' => route('payment.redirect', ['order_id' => $order->id]),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Order placed successfully.']);
    }
}
