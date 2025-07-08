<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\MenuInfo;
use App\Models\OrderInfo;
use App\Models\CustOrderInfo;
use Illuminate\Support\Facades\Validator;
 
class CartController extends Controller
{
    public function storeOrder(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'phone'            => 'required|string|max:15',
            'customer_email'   => 'nullable|email',
            'payment_method'   => 'required|in:cod',
            'type'             => 'required|in:delivery,takeaway',
            'address'          => 'required_if:type,delivery|nullable|string|max:500',
            'pincode'          => 'required_if:type,delivery|nullable|string|max:10',
            'cart'             => 'required|array|min:1',
            'cart.*.item_id'   => 'required|integer|exists:menu_items,id',
            'cart.*.name'      => 'required|string',
            'cart.*.price'     => 'required|numeric|min:0',
            'cart.*.quantity'  => 'required|integer|min:1',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }
 
        $validated = $validator->validated();
 
        // Step 2: Create customer order
        $order = CustOrderInfo::create([
            'name'            => $validated['name'],
            'phone'           => $validated['phone'],
            'customer_email'  => $validated['customer_email'] ?? null,
            'payment_method'  => $validated['payment_method'],
            'type'            => strtoupper($validated['type']),
            'status'          => 'pending',
            'pincode'         => $validated['type'] === 'delivery' ? ($validated['pincode'] ?? null) : null,
            'address'         => $validated['type'] === 'delivery' ? ($validated['address'] ?? null) : null,
        ]);
 
        // Step 3: Save each cart item and calculate charges
        $baseTotal = 0;
 
        foreach ($validated['cart'] as $item) {
            $menuItem = MenuInfo::find($item['item_id']);
 
            if ($menuItem) {
                $itemTotal = $menuItem->price * $item['quantity'];
                $baseTotal += $itemTotal;
 
                OrderInfo::create([
                    'cust_id'   => $order->id,
                    'order_id'  => $menuItem->id,
                    'qty'       => $item['quantity'],
                ]);
            }
        }
 
        // Step 4: Add GST and Café Charges
        $gstAmount = $baseTotal * 0.10;         // 10% GST
        $cafeCharge = $baseTotal * 0.10;        // 10% café charges
        $finalTotal = round($baseTotal + $gstAmount + $cafeCharge, 2);
 
        // Step 5: Update order total
        $order->update(['total' => $finalTotal]);
 
        // Step 6: Return response
        return response()->json([
            'status'   => 'success',
            'message'  => 'Order placed successfully!',
            'order_id' => $order->id,
            'total'    => $finalTotal,
        ], 201);
    }
}
 
 