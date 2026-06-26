<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventTemplateSchedule;
use App\Models\NotificationLog;
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
        NotificationSchedule::create(['event_id' => null, 'offset_minutes' => 1440, 'channel' => 'both']);
        EventTemplateSchedule::create([
            'event_template_id' => $signup->position->event->event_template_id,
            'offset_minutes' => 1440,
            'channel' => 'email',
        ]);

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertDatabaseHas('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => 1440,
            'type' => 'reminder:sms',
        ]);
        $this->assertDatabaseHas('notification_logs', [
            'signup_id' => $signup->id,
            'offset_minutes' => 1440,
            'type' => 'reminder:email',
        ]);
        // Exactly one text — the offset isn't processed once per source.
        $this->assertCount(1, $sms->sent);
    }

    /** A volunteer who hasn't opted in never gets a text, even on a 'both' schedule. */
    public function test_sms_skipped_when_not_opted_in(): void
    {
        Mail::fake();
        $sms = $this->fakeSmsSender();

        $signup = $this->makeSignup(smsOptIn: false);
        NotificationSchedule::create(['event_id' => null, 'offset_minutes' => 1440, 'channel' => 'both']);

        $this->artisan('reminders:send')->assertExitCode(0);

        $this->assertCount(0, $sms->sent);
        $this->assertDatabaseMissing('notification_logs', [
            'signup_id' => $signup->id,
            'type' => 'reminder:sms',
        ]);
    }

    private function makeSignup(bool $smsOptIn): Signup
    {
        $user = User::factory()->create([
            'sms_opt_in' => $smsOptIn,
            'phone' => '+18505551234',
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
            'starts_at' => now()->addHours(12),
            'ends_at' => now()->addHours(14),
            'is_published' => true,
        ]);
        $position = Position::create([
            'event_id' => $event->id,
            'category_id' => $category->id,
            'title' => 'Door',
            'slots_needed' => 1,
            'starts_at' => now()->addHours(12),
            'ends_at' => now()->addHours(14),
        ]);

        return Signup::create([
            'user_id' => $user->id,
            'position_id' => $position->id,
            'status' => 'confirmed',
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
