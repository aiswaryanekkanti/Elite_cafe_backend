<?php
 
namespace App\Http\Controllers;
 
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\Rules\Password;
 
class AuthController extends Controller
{
    public function signup(Request $request)
    {
 
 
        $validated = $request->validate([
            'first_name' => 'required|string|max:200',
            'last_name' => 'required|string|max:200',
            'phone_number' => 'required|digits:10',
            'email' => 'required|email|max:255',
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()     // at least one uppercase + lowercase
                    ->numbers()       // at least one number
                    ->symbols(),      // at least one special character
            ],
        ]);;
 
        $validated['password'] = Hash::make($validated['password']);
 
        $user = UserInfo::create($validated);
 
       try {
        $token = JWTAuth::fromUser($user);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
 
        return response()->json([
            'status'=>'success',
            'message' => 'User created successfully!',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 201);
    }
 
 
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required',  // can be email or phone number
            'password' => 'required',
        ]);
 
        $identifier = $credentials['identifier'];
        $password = $credentials['password'];
 
        // Check if identifier is an email or phone number
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            // identifier is email
            $user = UserInfo::where('email', $identifier)->first();
 
            if (!$user) {
                return response()->json([
                    'status'=>'failure',
                    'message' => 'Invalid credentials.',
                    'errors' => [
                        'identifier' => ['Email is incorrect.'],
                    ],
                ], 401);
            }
        } else {
            // identifier is phone number (basic validation, you can improve this)
            $user = UserInfo::where('phone_number', $identifier)->first();
 
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid credentials.',
                    'errors' => [
                        'identifier' => ['Phone number is incorrect.'],
                    ],
                ], 401);
            }
        }
 
        // Check password
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
                'errors' => [
                    'password' => ['Password is incorrect.'],
                ],
            ], 401);
        }
 
       try {
        $token = JWTAuth::fromUser($user);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }
 
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 3600,
        ]);
    }
 
    public function otp(Request $request)
    {
        //
    }
    public function profile(Request $request)
{
    $user = auth()->user();
    return response()->json($user);
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
}
 
 