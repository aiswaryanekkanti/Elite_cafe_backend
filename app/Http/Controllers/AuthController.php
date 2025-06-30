<?php

namespace App\Http\Controllers;

use App\Models\UserInfo;
use App\Models\CustomerLogin;  // You need to create this model/table for login history
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // User signup
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'first_name'   => 'required|string|max:200',
            'last_name'    => 'required|string|max:200',
            'phone_number' => 'required|digits:10|unique:user_infos,phone_number',
            'email'        => 'required|email|max:255|unique:user_infos,email',
            'password'     => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = UserInfo::create($validated);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json([
            'status'       => 'success',
            'message'      => 'User created successfully!',
            'user'         => $user,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
        ], 201);
    }

    // User login (by email or phone)
    public function login(Request $request)
{
    $credentials = $request->validate([
        'identifier' => 'required',  // email or phone number
        'password'   => 'required',
    ]);

    $identifier = $credentials['identifier'];
    $password = $credentials['password'];

    $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
        ? UserInfo::where('email', $identifier)->first()
        : UserInfo::where('phone_number', $identifier)->first();

    // Check if user exists and password matches
    if (!$user || !Hash::check($password, $user->password)) {
        return response()->json([
            'status'  => 'failure',
            'message' => 'Invalid credentials.',
        ], 401);
    }

    // **Add suspension check here:**
    
    if ($user->is_suspended) {
        return response()->json([
            'status'  => 'failure',
            'message' => 'Your account is suspended. Please contact support.',
        ], 403);
    }

    try {
        $token = JWTAuth::fromUser($user);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }

    // Log login history only if allowed
    CustomerLogin::create([
        'customer_email' => $user->email,
        'login_at'       => now(),
        'ip_address'     => $request->ip(),
        'device'         => $request->userAgent(),
    ]);

    return response()->json([
        'status'       => 'success',
        'message'      => 'Login successful',
        'user'         => $user,
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => JWTAuth::factory()->getTTL() * 60,
    ]);
}


    // Logout (invalidate token)
    public function logout(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            // Update logout time in login history if you want
            $lastLogin = CustomerLogin::where('customer_email', $user->email)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($lastLogin) {
                $lastLogin->update(['logout_at' => now()]);
            }

            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['status' => 'success', 'message' => 'Logged out successfully']);
        }

        return response()->json(['status' => 'failure', 'message' => 'No authenticated user'], 401);
    }

    // Fetch profile of authenticated user
    public function profile(Request $request)
    {
        return response()->json(['status' => 'success', 'user' => auth()->user()]);
    }

    // OTP placeholder
    public function otp(Request $request)
    {
        return response()->json(['message' => 'OTP functionality not implemented yet.']);
    }
}

// public function updateProfile(Request $request)
// {
//     $user = auth()->user();

//     $data = $request->validate([
//         'first_name'   => 'sometimes|string|max:200',
//         'last_name'    => 'sometimes|string|max:200',
//         'phone_number' => 'sometimes|digits:10|unique:user_infos,phone_number,' . $user->id,
//         'dob'          => 'sometimes|date|nullable',
//         'address'      => 'sometimes|string|nullable',
//         'gender'       => 'sometimes|in:Male,Female,Other|nullable',
//         'city'         => 'sometimes|string|nullable',
//         'state'        => 'sometimes|string|nullable',
//         'zip_code'     => 'sometimes|string|nullable',
//         // 'profile_photo'=> 'sometimes|image|max:2048|nullable',
//     ]);

//     // if ($request->hasFile('profile_photo')) {
//     //     // Delete old photo if exists
//     //     if ($user->profile_photo) {
//     //         Storage::disk('public')->delete($user->profile_photo);
//     //     }
//     //     $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
//     // }

//     $user->update($data);

//     return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
// }

 
 