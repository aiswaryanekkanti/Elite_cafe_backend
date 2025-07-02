<?php

namespace App\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ThankYouForSubscribing extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're Subscribed to Elite Cafe!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.thankyou',  // Correct view path here
        );
    }

    public function attachments(): array
    {
        return [];
    }

}
