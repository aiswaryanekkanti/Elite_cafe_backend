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

    // 8. Dashboard Summary
    public function summary()
    {
        $total = CustomerDetail::count();
        $deleted = CustomerDetail::where('is_deleted', 1)->count();
        $suspended = CustomerDetail::where('is_suspended', 1)->count();
        $active = CustomerDetail::where('is_deleted', 0)->where('is_suspended', 0)->count();

        return response()->json([
            'total_customers' => $total,
            'active_customers' => $active,
            'suspended_customers' => $suspended,
            'deleted_customers' => $deleted,
        ]);
    }

    // 9. Recently Registered Customers
    public function recentCustomers($limit = 5)
    {
        $recent = CustomerDetail::orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json(['recent_customers' => $recent]);
    }

    // 10. Top Active Customers by Login Count
    public function topActiveCustomers($limit = 5)
    {
        $top = CustomerLogin::selectRaw('customer_email, COUNT(*) as login_count')
            ->groupBy('customer_email')
            ->orderByDesc('login_count')
            ->limit($limit)
            ->get();

        return response()->json(['top_active_customers' => $top]);
    }

    // 11. Search Customers by name, email, or phone
    public function search(Request $request)
    {
        $query = $request->query('q');

        $results = CustomerDetail::where('name', 'LIKE', "%$query%")
            ->orWhere('email', 'LIKE', "%$query%")
            ->orWhere('phone', 'LIKE', "%$query%")
            ->get();

        return response()->json(['results' => $results]);
    }

    // 12. Paginated Customer List with Optional Filters
    public function paginatedList(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $status = $request->query('status');

        $query = CustomerDetail::query();

        if ($status === 'suspended') {
            $query->where('is_suspended', 1);
        } elseif ($status === 'deleted') {
            $query->where('is_deleted', 1);
        } elseif ($status === 'active') {
            $query->where('is_deleted', 0)->where('is_suspended', 0);
        }

        $customers = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($customers);
    }

    // 13. Get Detailed Info + Recent Logins for One Customer
    public function customerDetail($email)
    {
        $customer = CustomerDetail::findOrFail($email);
        $logins = CustomerLogin::where('customer_email', $email)
            ->orderByDesc('login_at')
            ->limit(10)
            ->get();

        return response()->json([
            'customer' => $customer,
            'recent_logins' => $logins,
        ]);
    }
}
