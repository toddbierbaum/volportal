<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// Monthly opportunity digest — 9am Central on the 1st of each month.
// Volunteers only receive this if opportunity_alerts_enabled is on (admin
// toggle on the Reminders page) and they personally opted in at signup.
Schedule::command('opportunities:send-alerts')
    ->monthlyOn(1, '09:00')
    ->withoutOverlapping();

// Daily digest of pending volunteers — 8am Central. Skipped when nobody
// is pending, so admins only get email on days that need their attention.
Schedule::command('volunteers:send-pending-digest')
    ->dailyAt('08:00')
    ->withoutOverlapping();
