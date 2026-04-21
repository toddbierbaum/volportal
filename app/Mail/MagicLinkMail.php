<?php

namespace App\Mail;

use App\Models\User;
use App\Support\EmailPreferencesLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public string $preferencesUrl;

    public function __construct(public User $user)
    {
        $this->loginUrl = URL::temporarySignedRoute(
            'magic-link.login',
            now()->addMinutes(30),
            ['user' => $user->id]
        );
        $this->preferencesUrl = EmailPreferencesLink::for($user);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your login link — Florida Chautauqua Theater',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.magic-link',
        );
    }
}
