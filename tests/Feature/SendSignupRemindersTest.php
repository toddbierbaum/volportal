<?php

namespace Tests\Feature;

use App\Mail\SignupReminderMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventTemplateSchedule;
use App\Models\NotificationSchedule;
use App\Models\Position;
use App\Models\Signup;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendSignupRemindersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression: a template's email-only schedule must not shadow a global
     * email+text schedule at the same offset. Channels union across sources,
     * so an SMS-eligible volunteer still gets their text.
     */
    public function test_template_email_schedule_does_not_shadow_global_sms_at_same_offset(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);

        // Same offset, conflicting channels: global wants both, template only email.
        $this->globalSchedule(1440, 'both');
        $this->templateSchedule($signup, 1440, 'email');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertSmsLogged($signup, 1440);
        $this->assertEmailLogged($signup, 1440);
        // Exactly one text — the offset isn't processed once per source.
        $this->assertCount(1, $sms->sent);
    }

    /** The reverse: a per-event SMS schedule adds a text on top of email-only sources. */
    public function test_per_event_sms_schedule_adds_text_to_email_only_sources(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'email');
        $this->templateSchedule($signup, 1440, 'email');
        $this->eventSchedule($signup, 1440, 'sms');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertSmsLogged($signup, 1440);
        $this->assertEmailLogged($signup, 1440);
        $this->assertCount(1, $sms->sent);
    }

    /** A 'both' schedule sends exactly one email and one text at that offset. */
    public function test_both_channel_sends_one_email_and_one_text(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(1, $sms->sent);
        Mail::assertSent(SignupReminderMail::class, 1);
        $this->assertSmsLogged($signup, 1440);
        $this->assertEmailLogged($signup, 1440);
    }

    /** An sms-only schedule sends a text and no email. */
    public function test_sms_only_channel_sends_text_without_email(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'sms');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(1, $sms->sent);
        Mail::assertNotSent(SignupReminderMail::class);
        $this->assertSmsLogged($signup, 1440);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => 1440,
            'type' => 'reminder:email',
        ]);
    }

    /** An email-only schedule never sends a text, even to an opted-in volunteer. */
    public function test_email_only_channel_sends_no_text(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'email');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'type' => 'reminder:sms',
        ]);
    }

    /** A volunteer who hasn't opted in never gets a text, even on a 'both' schedule. */
    public function test_sms_skipped_when_not_opted_in(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: false);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'type' => 'reminder:sms',
        ]);
    }

    /** Opted in but no phone on file: nothing to text, so no SMS. */
    public function test_sms_skipped_when_opted_in_but_no_phone(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true, phone: null);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'type' => 'reminder:sms',
        ]);
    }

    /**
     * Regression: a reminder must not re-send on subsequent cron runs. The
     * per-(signup, offset, type) NotificationLog is the dedup guard.
     */
    public function test_text_is_not_resent_on_repeated_runs(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);
        $this->artisan('reminders:send')->assertExitCode(0);
        $this->artisan('reminders:send')->assertExitCode(0);

        // Still exactly one text and one email despite three runs.
        $this->assertCount(1, $sms->sent);
        Mail::assertSent(SignupReminderMail::class, 1);
        $this->assertDatabaseCount('notification_logs', 2);
    }

    /**
     * The staleness guard's core case (and the root cause of the earlier
     * duplicate text): for an event ~12h out, a 'both' schedule at both the
     * 1-week and 1-day offsets should send ONLY the 1-day text — the 1-week
     * reminder's scheduled time is ~6 days past, so it's suppressed as stale
     * rather than firing on event day.
     */
    public function test_stale_long_lead_offset_is_suppressed_while_timely_one_sends(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        // ~12h out: the 1-day window is fresh, the 1-week window is long stale.
        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(10080, 'both');
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(1, $sms->sent);
        $this->assertSmsLogged($signup, 1440);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => 10080,
        ]);
    }

    /**
     * A long-lead reminder whose scheduled time is well past the grace period
     * is skipped entirely (no email, no text) — e.g. a "1 week before" reminder
     * for an event happening in ~12h.
     */
    public function test_stale_long_lead_reminder_is_skipped(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(10080, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        Mail::assertNotSent(SignupReminderMail::class);
        $this->assertDatabaseMissing('notification_logs', ['signup_id' => $signup->id]);
    }

    /**
     * A reminder still within the grace period of its scheduled time sends —
     * e.g. the 1-day reminder catching up on event morning (like the manual
     * remediation), where the 1440 offset is late by ~12h but within 24h grace.
     */
    public function test_reminder_within_grace_still_sends(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(1, $sms->sent);
        $this->assertSmsLogged($signup, 1440);
        $this->assertEmailLogged($signup, 1440);
    }

    /** The grace period is read from config — tightening it suppresses a send. */
    public function test_staleness_grace_is_configurable(): void
    {
        config(['reminders.max_staleness_minutes' => 60]);

        Mail::fake();
        $sms = $this->fakeSmsSender();

        // 1-day offset late by ~12h — allowed at the 24h default, but not at 60m.
        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', ['signup_id' => $signup->id]);
    }

    /** No reminder fires before its window opens (event further out than the offset). */
    public function test_no_text_before_window_opens(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        // Event is 48h out but the schedule only fires within 24h (1440m).
        $signup = $this->makeSignup(smsOptIn: true, hoursUntil: 48);
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', ['signup_id' => $signup->id]);
    }

    /** Cancelled signups are never reminded. */
    public function test_cancelled_signup_gets_no_text(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: true, status: 'cancelled');
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', ['signup_id' => $signup->id]);
    }

    // --- helpers -----------------------------------------------------------

    private function makeSignup(
        bool $smsOptIn = true,
        ?string $phone = '+18505551234',
        int $hoursUntil = 12,
        string $status = 'confirmed',
    ): Signup {
        $user = User::factory()->create([
            'sms_opt_in' => $smsOptIn,
            'phone' => $phone,
        ]);

        $template = EventTemplate::create(['name' => 'Storytellers', 'slug' => 'storytellers']);
        $category = Category::create([
            'name' => 'Front of House',
            'slug' => 'front-of-house',
            'event_template_id' => $template->id,
        ]);
        $event = Event::create([
            'event_template_id' => $template->id,
            'title' => 'Storytellers — June',
            'slug' => 'storytellers-june',
            'starts_at' => now()->addHours($hoursUntil),
            'ends_at' => now()->addHours($hoursUntil + 2),
            'is_published' => true,
        ]);
        $position = Position::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'title' => 'Door',
            'slots_needed' => 1,
            'starts_at' => now()->addHours($hoursUntil),
            'ends_at' => now()->addHours($hoursUntil + 2),
        ]);

        return Signup::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'status' => $status,
        ]);
    }

    private function globalSchedule(int $offset, string $channel): NotificationSchedule
    {
        return NotificationSchedule::create(['event_id' => null, 'offset_minutes' => $offset, 'channel' => $channel]);
    }

    private function eventSchedule(Signup $signup, int $offset, string $channel): NotificationSchedule
    {
        return NotificationSchedule::create([
            'event_id' => $signup->position->event->id,
            'offset_minutes' => $offset,
            'channel' => $channel,
        ]);
    }

    private function templateSchedule(Signup $signup, int $offset, string $channel): EventTemplateSchedule
    {
        return EventTemplateSchedule::create([
            'event_template_id' => $signup->position->event->event_template_id,
            'offset_minutes' => $offset,
            'channel' => $channel,
        ]);
    }

    private function assertSmsLogged(Signup $signup, int $offset): void
    {
        $this->assertDatabaseHas('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => $offset,
            'type' => 'reminder:sms',
        ]);
    }

    private function assertEmailLogged(Signup $signup, int $offset): void
    {
        $this->assertDatabaseHas('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => $offset,
            'type' => 'reminder:email',
        ]);
    }

    /** Bind a no-network SmsSender that records every send. */
    private function fakeSmsSender(): SmsSender
    {
        $fake = new class('sid', 'token', '+15550000000') extends SmsSender
        {
            /** @var list<string> */
            public array $sent = [];

            public function send(string $to, string $body): bool
            {
                $this->sent[] = $to;

                return true;
            }
        };

        $this->app->instance(SmsSender::class, $fake);

        return $fake;
    }
}
