<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItemStock;
use App\Models\MenuInfo;

class AdminStockController extends Controller
{
    // List stock info with menu details
    public function index()
    {
        $stocks = MenuItemStock::with('menuItem')->get();
        return response()->json($stocks);
    }

    // Update stock quantity and availability
    public function update(Request $request, $id)
    {
        $stock = MenuItemStock::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'is_available' => 'nullable|in:yes,no'
        ]);

        $stock->quantity = $validated['quantity'];

        // Auto-set availability based on quantity if not explicitly set
        if (!isset($validated['is_available'])) {
            $stock->is_available = $stock->quantity > 0 ? 'yes' : 'no';
        } else {
            $stock->is_available = $validated['is_available'];
        }

        $stock->save();

        return response()->json($stock);
    }
}
