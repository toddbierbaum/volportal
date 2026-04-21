<?php

namespace App\Mail;

use App\Models\EventTemplateSchedule;
use App\Models\NotificationSchedule;
use App\Models\Signup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignupReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Signup $signup,
        public NotificationSchedule|EventTemplateSchedule $schedule,
    ) {}

    public function envelope(): Envelope
    {
        $event = $this->signup->position->event;
        return new Envelope(
            subject: "Reminder: you're volunteering at {$event->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.signup-reminder',
        );
    }
}
