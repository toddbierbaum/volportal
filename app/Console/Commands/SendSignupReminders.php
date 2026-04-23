<?php

namespace App\Console\Commands;

use App\Mail\SignupReminderMail;
use App\Models\NotificationLog;
use App\Models\NotificationSchedule;
use App\Models\Signup;
use App\Support\SmsSender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('reminders:send {--dry-run : List what would be sent without sending}')]
#[Description('Send volunteer reminder emails + texts for signups whose event falls within a reminder schedule')]
class SendSignupReminders extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sms = SmsSender::fromConfig();

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

            // Merge all three sources, deduped by offset_minutes.
            // Preference: per-event > template > global (for the canonical
            // label + channel that drive dispatch).
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

                $schedule = $entry['schedule'];
                $channel = $schedule->channel ?? 'email';

                // Per-channel dedup: same offset on the same signup can send
                // once via email and once via SMS (e.g. channel=both).
                [$wantsEmail, $wantsSms] = match ($channel) {
                    'sms' => [false, true],
                    'both' => [true, true],
                    default => [true, false],
                };
                $smsEligible = $wantsSms && $signup->user->sms_opt_in && $signup->user->phone;

                $emailAlreadySent = $wantsEmail && NotificationLog::where('signup_id', $signup->id)
                    ->where('offset_minutes', $offsetMinutes)
                    ->where('type', 'reminder:email')
                    ->exists();
                $smsAlreadySent = $smsEligible && NotificationLog::where('signup_id', $signup->id)
                    ->where('offset_minutes', $offsetMinutes)
                    ->where('type', 'reminder:sms')
                    ->exists();

                if ($wantsEmail && ! $emailAlreadySent) {
                    if ($dryRun) {
                        $this->line(sprintf('[dry] [email] %s to %s — %s @ %s',
                            $schedule->label, $signup->user->email, $signup->position->title, $event->title,
                        ));
                    }
                    if (! $dryRun) {
                        $this->line(sprintf('→ [email] signup#%d offset=%d', $signup->id, $offsetMinutes));
                        Mail::to($signup->user->email)->send(new SignupReminderMail($signup, $schedule));
                        NotificationLog::create([
                            'signup_id' => $signup->id,
                            'notification_schedule_id' => in_array($entry['source'], ['global', 'event']) ? $schedule->id : null,
                            'offset_minutes' => $offsetMinutes,
                            'type' => 'reminder:email',
                            'sent_at' => now(),
                        ]);
                    }
                    $sent++;
                } elseif ($emailAlreadySent) {
                    $skipped++;
                }

                if ($smsEligible && ! $smsAlreadySent) {
                    $body = $this->smsBody($signup, $schedule);
                    if ($dryRun) {
                        $this->line(sprintf('[dry] [sms] %s to %s — %s @ %s',
                            $schedule->label, $signup->user->phone, $signup->position->title, $event->title,
                        ));
                    } else {
                        $this->line(sprintf('→ [sms] signup#%d offset=%d', $signup->id, $offsetMinutes));
                    }
                    $ok = $dryRun ? true : $sms->send($signup->user->phone, $body);
                    if ($ok) {
                        if (! $dryRun) {
                            NotificationLog::create([
                                'signup_id' => $signup->id,
                                'notification_schedule_id' => in_array($entry['source'], ['global', 'event']) ? $schedule->id : null,
                                'offset_minutes' => $offsetMinutes,
                                'type' => 'reminder:sms',
                                'sent_at' => now(),
                            ]);
                        }
                        $sent++;
                    }
                } elseif ($smsAlreadySent) {
                    $skipped++;
                }
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

    private function smsBody(Signup $signup, NotificationSchedule|\App\Models\EventTemplateSchedule $schedule): string
    {
        $event = $signup->position->event;
        $when = $signup->position->starts_at->format('D M j, g:i A');
        return sprintf(
            "%s reminder: %s — %s on %s%s. Reply STOP to opt out.",
            config('app.name'),
            $signup->position->title,
            $event->title,
            $when,
            $event->location ? ' at ' . $event->location : ''
        );
    }
}
