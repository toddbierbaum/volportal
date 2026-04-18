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
            ->with(['user', 'position.event.template.schedules'])
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
            $event = $signup->position->event;
            $eventSchedules = NotificationSchedule::where('event_id', $event->id)->get();
            $templateSchedules = $event->template?->schedules ?? collect();

            // Merge all three sources, deduped by offset_minutes. Preference
            // order when the same offset appears in multiple sources
            // (doesn't really matter for sending, but the canonical
            // schedule's label is what the email displays):
            //   per-event > template > global
            $byOffset = [];
            foreach ($globalSchedules as $s) {
                $byOffset[$s->offset_minutes] = ['source' => 'global', 'schedule' => $s];
            }
            foreach ($templateSchedules as $s) {
                $byOffset[$s->offset_minutes] = ['source' => 'template', 'schedule' => $s];
            }
            foreach ($eventSchedules as $s) {
                $byOffset[$s->offset_minutes] = ['source' => 'event', 'schedule' => $s];
            }

            foreach ($byOffset as $offsetMinutes => $entry) {
                $minutesUntilPosition = now()->diffInMinutes($signup->position->starts_at, false);

                if ($minutesUntilPosition > $offsetMinutes || $minutesUntilPosition < 0) {
                    continue;
                }

                $alreadySent = NotificationLog::where('signup_id', $signup->id)
                    ->where('offset_minutes', $offsetMinutes)
                    ->exists();

                if ($alreadySent) {
                    $skipped++;
                    continue;
                }

                $schedule = $entry['schedule'];

                $this->line(sprintf(
                    '%s Reminder (%s, %s) to %s for %s @ %s',
                    $dryRun ? '[dry]' : '→',
                    $schedule->label,
                    $entry['source'],
                    $signup->user->email,
                    $signup->position->title,
                    $event->title,
                ));

                if (! $dryRun) {
                    Mail::to($signup->user->email)->send(new SignupReminderMail($signup, $schedule));
                    NotificationLog::create([
                        'signup_id' => $signup->id,
                        'notification_schedule_id' => $entry['source'] === 'global' || $entry['source'] === 'event'
                            ? $schedule->id
                            : null,
                        'offset_minutes' => $offsetMinutes,
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
