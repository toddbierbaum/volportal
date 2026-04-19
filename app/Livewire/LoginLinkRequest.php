<?php

namespace App\Livewire;

use App\Mail\MagicLinkMail;
use App\Models\User;
use App\Support\EmailSendThrottle;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginLinkRequest extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    // Honeypot — hidden in the DOM, legit users never touch it. Bots fill
    // every input. Non-empty value means "silently drop this request".
    public string $website = '';

    public bool $sent = false;

    public function mount(): void
    {
        $cookieId = request()->cookie('volunteer_id');
        if ($cookieId) {
            $user = User::find($cookieId);
            if ($user && ! $user->isAdmin()) {
                $this->email = $user->email;
            }
        }
    }

    public function send(): void
    {
        // Honeypot trip: pretend the request succeeded so scrapers don't
        // learn they were detected. No email, no rate-limiter credit spent.
        if ($this->website !== '') {
            $this->sent = true;
            return;
        }

        $this->validate();

        if (! EmailSendThrottle::allow($this->email, request()->ip())) {
            $this->addError('email', 'Too many requests. Please wait an hour before trying again.');
            return;
        }

        $user = User::where('email', $this->email)->first();
        if ($user && ! $user->isAdmin()) {
            Mail::to($user->email)->send(new MagicLinkMail($user));
        }

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.login-link-request');
    }
}
