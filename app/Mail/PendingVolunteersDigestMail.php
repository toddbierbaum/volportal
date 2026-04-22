<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PendingVolunteersDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reviewUrl;

    /**
     * @param  Collection<int, \App\Models\User>  $pending
     */
    public function __construct(
        public User $admin,
        public Collection $pending,
    ) {
        $this->reviewUrl = route('admin.volunteers.index', ['status' => 'pending']);
    }

    public function envelope(): Envelope
    {
        $count = $this->pending->count();
        return new Envelope(
            subject: $count === 1
                ? '1 volunteer awaiting your approval'
                : "{$count} volunteers awaiting your approval",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pending-volunteers-digest',
        );
    }
}
