<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VolunteerApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(public User $user)
    {
        $this->loginUrl = URL::temporarySignedRoute(
            'magic-link.login',
            now()->addDays(7),
            ['user' => $user->id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're approved — pick your shifts",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.volunteer-approved',
        );
    }
}
