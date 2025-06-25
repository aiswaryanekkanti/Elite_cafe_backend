<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerDetail;

class CustomerDetailController extends Controller
{
    public function update(Request $request, $email)
    {
        $customer = CustomerDetail::find($email);
        if (!$customer) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $validated = $request->validate([
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'phone_number' => 'nullable|string|max:20',
            // 'profile_photo' => 'nullable|image|max:2048',
        ]);

        // Handle file upload (commented out for now)
        // if ($request->hasFile('profile_photo')) {
        //     $file = $request->file('profile_photo');
        //     $filename = time() . '_' . $file->getClientOriginalName();
        //     $path = $file->storeAs('profile_photos', $filename, 'public');
        //     $customer->profile_photo = $path;
        // }

        // Update other fields
        foreach ($validated as $key => $val) {
            if (!is_null($val)) {
                $customer->$key = $val;
            }
        }

        $customer->save();

        return response()->json(['message' => 'Profile updated.', 'user' => $customer]);
    }
}
