<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use App\Models\CustomerDetail;

class UserController extends Controller
{
    // List all users
    

// public function index()
// {
//     // Get only users with role "customer"
//     $users = CustomerDetail::where('role', 'customer')
//         ->select('email', 'first_name', 'last_name', 'phone_number', 'created_at')
//         ->get();

//     return response()->json(['data' => $users]);
// }

// Use 'email' as identifier in methods instead of id

    // rest of code ...



    // Block or unblock a user
    public function block($email)
{
    $user = CustomerDetail::findOrFail($email);

        // Prevent blocking yourself or other admins, optionally
        if ($user->role === 'admin') {
            return response()->json(['error' => 'Cannot block an admin user'], 403);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json(['message' => $user->is_blocked ? 'User blocked' : 'User unblocked']);
    }

    // Assign a new role to the user
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,staff,customer',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return response()->json(['message' => 'Role updated']);
    }

    // Send password reset link to user email
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent']);
        } else {
            return response()->json(['error' => 'Failed to send reset link'], 500);
        }
    }

    // Fetch login/logout activity logs for user
    public function activity($id)
    {
        // Assuming you have a table `user_activities` with user_id, action, timestamp, ip_address, user_agent

        $activities = DB::table('user_activities')
            ->where('user_id', $id)
            ->orderBy('timestamp', 'desc')
            ->get(['timestamp', 'action', 'ip_address', 'user_agent']);

        return response()->json(['data' => $activities]);
    }
}
