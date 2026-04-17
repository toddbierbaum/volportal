<?php

namespace App\Livewire;

use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class WelcomeBanner extends Component
{
    public ?int $userId = null;
    public ?string $firstName = null;
    public bool $sent = false;

    public function mount(): void
    {
        if (auth()->check()) {
            return;
        }

        $cookieId = request()->cookie('volunteer_id');
        if (! $cookieId) {
            return;
        }

        $user = User::find($cookieId);
        if ($user && ! $user->isAdmin()) {
            $this->userId = $user->id;
            $this->firstName = explode(' ', $user->name)[0] ?: $user->name;
        }
    }

    public function sendLink(): void
    {
        if (! $this->userId) {
            return;
        }

        $user = User::find($this->userId);
        if ($user && ! $user->isAdmin()) {
            Mail::to($user->email)->send(new MagicLinkMail($user));
            $this->sent = true;
        }
    }

    public function dismiss(): void
    {
        Cookie::queue(Cookie::forget('volunteer_id'));
        $this->userId = null;
        $this->firstName = null;
    }

    public function render()
    {
        return view('livewire.welcome-banner');
    }
}
