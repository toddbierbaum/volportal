<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiters();

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', 'throttle:livewire.update']);
        });
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('password.email', fn (Request $r) => Limit::perMinute(20)->by('password-email:'.$r->ip()));

        RateLimiter::for('password.reset.form', fn (Request $r) => Limit::perMinute(60)->by('password-reset-form:'.$r->ip()));

        RateLimiter::for('livewire.update', fn (Request $r) => Limit::perMinute(60)->by('livewire-update:'.($r->user()?->getAuthIdentifier() ?? $r->ip())));
    }
}
