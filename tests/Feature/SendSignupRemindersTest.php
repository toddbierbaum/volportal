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
     * Documents tonight's duplicate-text root cause: a 'both' schedule at two
     * offsets is two independent reminders, so an SMS-eligible volunteer gets
     * a distinct text per offset (one at 1 week, one at 1 day), each logged
     * under its own offset.
     */
    public function test_each_offset_is_an_independent_text(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        // Event is ~12h out, so both the 1-week and 1-day windows are open.
        $signup = $this->makeSignup(smsOptIn: true);
        $this->globalSchedule(10080, 'both');
        $this->globalSchedule(1440, 'both');

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(2, $sms->sent);
        $this->assertSmsLogged($signup, 10080);
        $this->assertSmsLogged($signup, 1440);
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
