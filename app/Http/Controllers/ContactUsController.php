<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ContactUs;
 
class ContactUsController extends Controller
{
public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string',
        'last_name'  => 'required|string',
        'email'      => 'required|email',
        'outlet'     => ['required', Rule::in(['Banjara Hills', 'Gachibowli', 'Jubilee Hills'])],
        'subject'    => 'nullable|string',
        'message'    => 'required|string',
    ]);

    $contact = ContactUs::create($validated);

    // return response()->json([
    //     'status'=>'Success',
    //     'message' => 'Contact message saved successfully',
    //     'data' => $contact
    // ], 201);
    return redirect()->back()->with('success', 'Your message has been sent successfully!');
}


    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'first_name' => 'required|string',
    //         'last_name'  => 'required|string',
    //         'email'      => 'required|email',
    //         'outlet'     => ['required', Rule::in(['Banjara Hills', 'Gachibowli', 'Jubilee Hills'])],
    //         'subject'    => 'nullable|string',
    //         'message'    => 'required|string',
    //     ]);
 
    //     ContactUs::create($validated);
    //     return response()->json([
    //             'message' => 'User Data saved',
    //             'users' => $validated
    //         ], 201);
 
    //     // Flash success message to session
    //     // return back()->with('success', 'Your message has been sent successfully!');
    // }
}
 