<?php

namespace App\Livewire;

use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LoginLinkRequest extends Component
{
    #[Validate('required|email')]
    public string $email = '';

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
        $this->validate();

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
