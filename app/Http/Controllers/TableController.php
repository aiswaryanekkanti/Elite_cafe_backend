<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\TableInfo;
use App\Models\CountInfo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
 
class TableController extends Controller
{
    // public function reservationdetails(Request $request)
    // {
    //     // // ✅ OTP verification check
    //     // if (!session('reservation_otp_verified') || session('reservation_otp_phone') !== $request->phone_number) {
    //     //     return response()->json([
    //     //         'status'  => 'error',
    //     //         'message' => 'Please verify your phone number using OTP before reservation.',
    //     //     ], 403);
    //     // }
 
    //     // ✅ Validate incoming request
    //     $validated = $request->validate([
    //         'first_name'        => 'required|string|max:200',
    //         'last_name'         => 'required|string|max:300',
    //         'email'             => 'required|email|max:200',
    //         'phone_number'      => 'required|digits:10',
    //         'persons'           => 'required|string|max:10', // e.g. '1-4', '5-8'
    //         'reservation_date'  => 'required|date',
    //         'reservation_time'  => 'required|date_format:H:i',
    //         'message'           => 'nullable|string|max:300',
    //     ]);
 
    //     // 🔧 Format reservation_time to MySQL TIME (H:i:s)
    //     $validated['reservation_time'] = date("H:i:s", strtotime($validated['reservation_time']));
 
    //     // 🔍 Step 1: Check availability from table_reservation_count
    //     $available = CountInfo::where('seats', $validated['persons'])
    //         ->where('time_slot', $validated['reservation_time'])
    //         ->where('count', '>', 0)
    //         ->first();
 
    //     if (!$available) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Sorry, this time slot is fully booked.',
    //         ], 400);
    //     }
 
    //     // ✅ Step 2: Create reservation
    //     $reservation = TableInfo::create($validated);
 
    //     // 🔁 Step 3: Decrement available count
    //     CountInfo::where('seats', $validated['persons'])
    //         ->where('time_slot', $validated['reservation_time'])
    //         ->decrement('count');
 
    //     // ✅ Optional: clear OTP session after reservation success
    //     session()->forget('reservation_otp_verified');
    //     session()->forget('reservation_otp_phone');
 
    //     return response()->json([
    //         'status'  => 'success',
    //         'message' => 'Reservation Successfully',
    //         'data'    => $reservation,
    //     ], 201);
    // }
 
 
 
    // public function sendOtp(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits:10',
    //     ]);
 
    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'message' => 'Phone number is required and must be 10 digits'], 422);
    //     }
 
    //     $phone = $request->phone_number;
    //     $otp = rand(100000, 999999);
    //     $expiry = now()->addMinutes((int) env('OTP_EXPIRY_MINUTES', 5));
 
 
    //     try {
    //         // Send via Twilio
    //         $sid = env('TWILIO_SID');
    //         $token = env('TWILIO_AUTH_TOKEN');
    //         $from = env('TWILIO_PHONE');
    //         $twilio = new Client($sid, $token);
 
    //         $to = '+91' . $phone; // Adjust country code if needed
 
    //         $twilio->messages->create($to, [
    //             'from' => $from,
    //             'body' => "Your OTP is $otp",
    //         ]);
 
    //         Cache::put('reservation_otp_' . $phone, $otp, $expiry);
 
    //         return response()->json(['status' => true, 'message' => 'OTP sent successfully']);
    //     } catch (\Exception $e) {
    //         Log::error('Reservation OTP error', ['phone' => $phone, 'error' => $e->getMessage()]);
    //         return response()->json(['status' => false, 'message' => 'Failed to send OTP'], 500);
    //     }
    // }
    // public function verifyOtp(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone_number' => 'required|digits:10',
    //         'otp' => 'required|digits:6',
    //     ]);
 
    //     if ($validator->fails()) {
    //         return response()->json(['status' => false, 'message' => 'Invalid input'], 422);
    //     }
 
    //     $phone = $request->phone_number;
    //     $otp = $request->otp;
 
    //     $storedOtp = Cache::get('reservation_otp_' . $phone);
 
    //     if ($storedOtp && $storedOtp == $otp) {
    //         // OTP is valid — mark verified in session
    //         session(['reservation_otp_verified' => true]);
    //         session(['reservation_otp_phone' => $phone]);
    //         Cache::forget('reservation_otp_' . $phone);
 
    //         return response()->json(['status' => true, 'message' => 'OTP verified successfully']);
    //     }
 
    //     return response()->json(['status' => false, 'message' => 'Invalid or expired OTP'], 401);
    // }
 
 
 
 
public function reservationdetails(Request $request)
{
    // ✅ Validate incoming request
    $validated = $request->validate([
        'first_name'        => 'required|string|max:200',
        'last_name'         => 'required|string|max:300',
        'email'             => 'required|email|max:200',
        'phone_number'      => 'required|digits:10',
        'persons'           => 'required|string|max:10', // e.g. '1-4', '5-8'
        'reservation_date'  => 'required|date',
        'reservation_time'  => 'required|date_format:H:i',
        'message'           => 'nullable|string|max:300',
    ]);
 
    // 🔧 Format reservation_time to MySQL TIME (H:i:s)
    $validated['reservation_time'] = date("H:i:s", strtotime($validated['reservation_time']));
    $reservationDate = $validated['reservation_date'];
    $reservationTime = $validated['reservation_time'];
    $persons = $validated['persons'];
 
    // 🔍 Check if reservation is for today
    $isToday = date('Y-m-d') === $reservationDate;
 
    if ($isToday) {
        // ✅ For today: check availability from table_reservation_count
        $available = CountInfo::where('seats', $persons)
            ->where('time_slot', $reservationTime)
            ->where('count', '>', 0)
            ->first();
 
        if (!$available) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, this time slot is fully booked.',
            ], 400);
        }
    } else {
        // ✅ For future date: check count from table_reservation directly
        $existing = TableInfo::where('reservation_date', $reservationDate)
            ->where('reservation_time', $reservationTime)
            ->where('persons', $persons)
            ->count();
 
        if ($existing >= 3) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sorry, this time slot is fully booked for that date.',
            ], 400);
        }
    }
 
    // ✅ Save reservation
    $reservation = TableInfo::create($validated);
 
    // 🔁 Decrement only for today
    if ($isToday) {
        CountInfo::where('seats', $persons)
            ->where('time_slot', $reservationTime)
            ->decrement('count');
    }
 
    // ✅ Clear OTP session
    session()->forget('reservation_otp_verified');
    session()->forget('reservation_otp_phone');
 
    return response()->json([
        'status'  => 'success',
        'message' => 'Reservation successfully created.',
        'data'    => $reservation,
    ], 201);
}
 public function tableinfo()
{
    $reservations = TableInfo::all();

    return response()->json([
        'status' => 'success',
        'data' => $reservations,
    ]);
}

 
 
}
 
 