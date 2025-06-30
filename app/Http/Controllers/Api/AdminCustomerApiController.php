<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerDetail;
use App\Models\CustomerLogin;
use Illuminate\Http\Request;

class AdminCustomerApiController extends Controller
{
    // 1. Get all customers
    public function index()
    {
        $customers = CustomerDetail::all();
        return response()->json(['data' => $customers]);
    }

    // 2. Soft delete customer
    public function softDelete($email)
    {
        $customer = CustomerDetail::findOrFail($email);
        $customer->is_deleted = 1;
        $customer->save();

        return response()->json(['message' => 'Customer soft-deleted successfully.']);
    }

    // 3. Restore customer
    public function restore($email)
    {
        $customer = CustomerDetail::findOrFail($email);
        $customer->is_deleted = 0;
        $customer->save();

        return response()->json(['message' => 'Customer restored successfully.']);
    }

    // 4. Suspend customer
    public function suspend($email)
    {
        $customer = CustomerDetail::findOrFail($email);
        $customer->is_suspended = 1;
        $customer->save();

        return response()->json(['message' => 'Customer account suspended.']);
    }

    // 5. Unsuspend customer
    public function unsuspend($email)
    {
        $customer = CustomerDetail::findOrFail($email);
        $customer->is_suspended = 0;
        $customer->save();

        return response()->json(['message' => 'Customer account unsuspended.']);
    }

    // 6. Update admin notes
    public function updateNotes(Request $request, $email)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $customer = CustomerDetail::findOrFail($email);
        $customer->admin_notes = $request->admin_notes;
        $customer->save();

        return response()->json(['message' => 'Admin notes updated successfully.']);
    }

    // 7. View login history
    public function loginHistory($email)
    {
        $logins = CustomerLogin::where('customer_email', $email)
            ->orderByDesc('login_at')
            ->get();

        return response()->json(['email' => $email, 'logins' => $logins]);
    }
}
