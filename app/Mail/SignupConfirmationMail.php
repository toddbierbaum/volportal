<?php

namespace App\Mail;

use App\Models\User;
use App\Support\EmailPreferencesLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SignupConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $preferencesUrl;

    /**
     * @param  Collection<int, \App\Models\Signup>  $signups
     */
    public function __construct(
        public User $user,
        public Collection $signups,
    ) {
        $this->preferencesUrl = EmailPreferencesLink::for($user);
    }

    public function envelope(): Envelope
    {
        $count = $this->signups->count();
        return new Envelope(
            subject: $count > 0
                ? "You're signed up to volunteer — Florida Chautauqua Theater"
                : "Welcome to the volunteer list — Florida Chautauqua Theater",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.signup-confirmation',
        );
    }
}
