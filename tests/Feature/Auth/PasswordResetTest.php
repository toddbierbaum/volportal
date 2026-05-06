<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;
use Mockery;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertSeeVolt('pages.auth.forgot-password')
            ->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response
                ->assertSeeVolt('pages.auth.reset-password')
                ->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $component = Volt::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'password')
                ->set('password_confirmation', 'password');

            $component->call('resetPassword');

            $component
                ->assertRedirect('/login')
                ->assertHasNoErrors();

            return true;
        });
    }

    public function test_forgot_password_blocks_after_email_send_limit(): void
    {
        config(['auth.passwords.users.throttle' => 0]);

        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $errors = $component->errors();
        $this->assertArrayHasKey('email', $errors->toArray());
        $this->assertStringContainsString('info@fcweb.org', $errors->first('email'));
        $this->assertStringContainsString('reference ', $errors->first('email'));

        Notification::assertSentToTimes($user, ResetPassword::class, 2);
    }

    public function test_reset_password_blocks_after_repeated_failures(): void
    {
        for ($i = 0; $i < 10; $i++) {
            Volt::test('pages.auth.reset-password', ['token' => 'invalid-token-'.$i])
                ->set('email', 'someone@example.com')
                ->set('password', 'password')
                ->set('password_confirmation', 'password')
                ->call('resetPassword');
        }

        $component = Volt::test('pages.auth.reset-password', ['token' => 'still-invalid'])
            ->set('email', 'someone@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $errors = $component->errors();
        $this->assertArrayHasKey('email', $errors->toArray());
        $this->assertStringContainsString('info@fcweb.org', $errors->first('email'));
        $this->assertStringContainsString('reference ', $errors->first('email'));
    }

    public function test_reset_password_clears_throttle_on_success(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        Volt::test('pages.auth.reset-password', ['token' => 'wrong-token'])
            ->set('email', $user->email)
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');

        $key = 'password-update:127.0.0.1';
        $this->assertSame(1, RateLimiter::attempts($key));

        Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user, $key) {
            Volt::test('pages.auth.reset-password', ['token' => $notification->token])
                ->set('email', $user->email)
                ->set('password', 'newpassword')
                ->set('password_confirmation', 'newpassword')
                ->call('resetPassword')
                ->assertHasNoErrors();

            $this->assertSame(0, RateLimiter::attempts($key));

            return true;
        });
    }

    public function test_lockout_logger_writes_structured_log_entry(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'rate-limit.lockout'
                    && $context['limiter'] === 'password.update'
                    && ! empty($context['id']);
            });

        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit('password-update:127.0.0.1', 600);
        }

        Volt::test('pages.auth.reset-password', ['token' => 'irrelevant'])
            ->set('email', 'a@b.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('resetPassword');
    }
}
