<?php

namespace App\Console\Commands;

use App\Mail\SignupReminderMail;
use App\Models\NotificationLog;
use App\Models\NotificationSchedule;
use App\Models\Signup;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('reminders:send {--dry-run : List what would be sent without sending}')]
#[Description('Send volunteer reminder emails for signups whose event falls within a reminder schedule')]
class SendSignupReminders extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $signups = Signup::query()
            ->with(['user', 'position.event'])
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '>=', now()))
            ->get();

        if ($signups->isEmpty()) {
            $this->info('No upcoming signups to consider.');
            return self::SUCCESS;
        }

        $globalSchedules = NotificationSchedule::whereNull('event_id')->get();
        $sent = 0;
        $skipped = 0;

        foreach ($signups as $signup) {
            $eventId = $signup->position->event_id;
            $eventSchedules = NotificationSchedule::where('event_id', $eventId)->get();
            $schedules = $globalSchedules->concat($eventSchedules);

            foreach ($schedules as $schedule) {
                $minutesUntilPosition = now()->diffInMinutes($signup->position->starts_at, false);

                if ($minutesUntilPosition > $schedule->offset_minutes || $minutesUntilPosition < 0) {
                    continue;
                }

                $alreadySent = NotificationLog::where('signup_id', $signup->id)
                    ->where('notification_schedule_id', $schedule->id)
                    ->exists();

                if ($alreadySent) {
                    $skipped++;
                    continue;
                }

                $this->line(sprintf(
                    '%s Reminder (%s) to %s for %s @ %s',
                    $dryRun ? '[dry]' : '→',
                    $schedule->label,
                    $signup->user->email,
                    $signup->position->title,
                    $signup->position->event->title,
                ));

                if (! $dryRun) {
                    Mail::to($signup->user->email)->send(new SignupReminderMail($signup, $schedule));
                    NotificationLog::create([
                        'signup_id' => $signup->id,
                        'notification_schedule_id' => $schedule->id,
                        'type' => 'reminder',
                        'sent_at' => now(),
                    ]);
                }

                $sent++;
            }
        }

        $this->info(sprintf(
            '%s %d reminder%s, skipped %d already-sent.',
            $dryRun ? 'Would send' : 'Sent',
            $sent,
            $sent === 1 ? '' : 's',
            $skipped,
        ));

        return self::SUCCESS;
    }
}
