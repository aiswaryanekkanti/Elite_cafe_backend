<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;
use App\Mail\ThankYouForSubscribing;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email'
        ]);

        // Save the subscriber
        NewsletterSubscriber::create([
            'email' => $request->email,
            'subscribed_at' => now()
        ]);

        // Send confirmation email
        Mail::to($request->email)->send(new ThankYouForSubscribing());

        return response()->json(['message' => 'Thank you for subscribing!'], 200);
    }
}
