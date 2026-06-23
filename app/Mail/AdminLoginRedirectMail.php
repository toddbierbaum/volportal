<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLoginRedirectMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(public User $admin)
    {
        // Admins sign in at the same /login page as everyone, but with a
        // password (+ TOTP) — not a volunteer magic link. Static page, no
        // signed URL needed.
        $this->loginUrl = route('login');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Logging in to your admin account — Florida Chautauqua Theater',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-login-redirect',
        );
    }
}
