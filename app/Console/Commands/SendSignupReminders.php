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
    public function handle(SmsSender $sms): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $maxStaleness = (int) config('reminders.max_staleness_minutes');

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

            $byOffset = $this->mergeSchedules($globalSchedules, $templateSchedules, $eventSchedules);

            foreach ($byOffset as $offsetMinutes => $entry) {
                $minutesUntilPosition = now()->diffInMinutes($signup->position->starts_at, false);

                // Skip when the window hasn't opened yet, the event has already
                // passed, or the reminder's scheduled time (offset before the
                // position) is more than the grace period in the past — a stale
                // long-lead reminder (e.g. "1 week before") shouldn't fire on
                // event day just because it was never sent.
                if ($minutesUntilPosition > $offsetMinutes
                    || $minutesUntilPosition < 0
                    || $minutesUntilPosition < $offsetMinutes - $maxStaleness) {
                    continue;
                }

                $schedule = $entry['schedule'];
                $wantsEmail = $entry['wantsEmail'];
                $wantsSms = $entry['wantsSms'];
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

    /**
     * Collapse global, template, and per-event schedules into one entry per
     * offset. Channels are UNIONed across sources — if any applicable source
     * at an offset requests a channel, it's sent — so a more specific source
     * can never silently drop a channel a broader one requested (e.g. a
     * template's email-only schedule shadowing a global email+text one at the
     * same offset). The most specific source present (event > template >
     * global) supplies the canonical label + schedule identity.
     *
     * @return array<int, array{source: string, schedule: NotificationSchedule|\App\Models\EventTemplateSchedule, wantsEmail: bool, wantsSms: bool}>
     */
    private function mergeSchedules(iterable $global, iterable $template, iterable $event): array
    {
        $byOffset = [];

        foreach (['global' => $global, 'template' => $template, 'event' => $event] as $source => $schedules) {
            foreach ($schedules as $s) {
                $offset = $s->offset_minutes;
                [$email, $sms] = $this->channelFlags($s->channel ?? 'email');

                $byOffset[$offset] ??= ['source' => $source, 'schedule' => $s, 'wantsEmail' => false, 'wantsSms' => false];
                // Later (more specific) sources own the canonical label + id...
                $byOffset[$offset]['source'] = $source;
                $byOffset[$offset]['schedule'] = $s;
                // ...but channels accumulate across every source.
                $byOffset[$offset]['wantsEmail'] = $byOffset[$offset]['wantsEmail'] || $email;
                $byOffset[$offset]['wantsSms'] = $byOffset[$offset]['wantsSms'] || $sms;
            }
        }

        return $byOffset;
    }

    /**
     * Map a channel string to [wantsEmail, wantsSms].
     *
     * @return array{0: bool, 1: bool}
     */
    private function channelFlags(string $channel): array
    {
        return match ($channel) {
            'sms' => [false, true],
            'both' => [true, true],
            default => [true, false],
        };
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
