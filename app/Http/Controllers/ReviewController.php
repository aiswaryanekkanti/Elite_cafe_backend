<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\ReviewInfo;
 
 
class ReviewController extends Controller
{
 
public function reviewdata(Request $request)
{
    // Validate the request
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name'  => 'required|string|max:255',
        'review'     => 'required|string|max:1000',
    ]);
    // Store the data
    $review = ReviewInfo::create($validated);
 
    // Return JSON response
    return response()->json([
        'message' => 'Review submitted successfully!',
        'data' => $review
    ], 201); // 201 Created
}
 
}
 
 