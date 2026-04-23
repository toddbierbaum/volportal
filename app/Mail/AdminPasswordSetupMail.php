<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AdminPasswordSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setupUrl;

    public function __construct(public User $admin)
    {
        $this->setupUrl = URL::temporarySignedRoute(
            'admin.password-setup',
            now()->addHours(24),
            ['admin' => $admin->id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set your password — Florida Chautauqua Theater',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-password-setup',
        );
    }
}
