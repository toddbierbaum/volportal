<?php

namespace Tests\Feature;

use App\Livewire\LoginLinkRequest;
use App\Livewire\VolunteerSignup;
use App\Mail\AdminLoginRedirectMail;
use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_link_emails_admin_a_redirect_not_a_magic_link(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);

        Livewire::test(LoginLinkRequest::class)
            ->set('email', $admin->email)
            ->call('send')
            ->assertSet('sent', true);

        Mail::assertSent(AdminLoginRedirectMail::class);
        Mail::assertNotSent(MagicLinkMail::class);
    }

    public function test_login_link_emails_volunteer_a_magic_link(): void
    {
        Mail::fake();
        $volunteer = User::factory()->create(['role' => 'volunteer', 'email' => 'vol@example.com']);

        Livewire::test(LoginLinkRequest::class)
            ->set('email', $volunteer->email)
            ->call('send')
            ->assertSet('sent', true);

        Mail::assertSent(MagicLinkMail::class);
        Mail::assertNotSent(AdminLoginRedirectMail::class);
    }

    public function test_signup_emails_existing_admin_a_redirect_not_a_magic_link(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'email' => 'admin2@example.com']);

        Livewire::test(VolunteerSignup::class)
            ->set('name', 'Some Admin')
            ->set('email', $admin->email)
            ->set('phone', '(850) 555-1234')
            ->call('proceedToCategories')
            ->assertSet('step', 5);

        Mail::assertSent(AdminLoginRedirectMail::class);
        Mail::assertNotSent(MagicLinkMail::class);
    }

    public function test_signup_emails_existing_volunteer_a_magic_link(): void
    {
        Mail::fake();
        $volunteer = User::factory()->create(['role' => 'volunteer', 'email' => 'vol2@example.com']);

        Livewire::test(VolunteerSignup::class)
            ->set('name', 'Returning Volunteer')
            ->set('email', $volunteer->email)
            ->set('phone', '(850) 555-1234')
            ->call('proceedToCategories')
            ->assertSet('step', 5);

        Mail::assertSent(MagicLinkMail::class);
        Mail::assertNotSent(AdminLoginRedirectMail::class);
    }
}
