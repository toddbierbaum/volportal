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
use Illuminate\Support\Facades\URL;

class OpportunityAlertsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $dashboardUrl;

    public string $preferencesUrl;

    /**
     * @param  Collection<int, \App\Models\Position>  $positions
     */
    public function __construct(
        public User $user,
        public Collection $positions,
    ) {
        // Magic-link lands them on /my with opportunities visible. 7-day
        // lifetime mirrors the approval email — long enough to be useful
        // without outliving the month this alert is for.
        $this->dashboardUrl = URL::temporarySignedRoute(
            'magic-link.login',
            now()->addDays(7),
            ['user' => $user->id]
        );
        $this->preferencesUrl = EmailPreferencesLink::for($user);
    }

    public function envelope(): Envelope
    {
        $count = $this->positions->count();
        return new Envelope(
            subject: $count === 1
                ? "1 open volunteer shift matches your interests"
                : "{$count} open volunteer shifts match your interests",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.opportunity-alerts',
        );
    }
}
