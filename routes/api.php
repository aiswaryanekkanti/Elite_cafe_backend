<?php
 
use App\Http\Controllers\ContactUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AuthController;

 
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/contact', [ContactUsController::class, 'store']); 

// Route::get('/menu-items', [MenuItemController::class, 'index']);
 
// Route::get('/menu-items', [MenuItemController::class, 'index']);

// Route::post('/menu-items', [MenuItemController::class, 'store']);


Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
Route::post('/signup',[AuthController::class, 'signup']);
Route::post('/login',[AuthController::class, 'login']);
Route::post('/verify-otp',[AuthController::class, 'otp']);
Route::post('/send-otp',[AuthController::class, 'otp']);
