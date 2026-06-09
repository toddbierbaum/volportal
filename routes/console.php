<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// appendOutputTo captures each scheduled command's stdout+stderr to a side
// file we can grep, regardless of how the cron line redirects (DreamHost's
// cron pipes to /dev/null). Without this, scheduled jobs are invisible.
$schedulerLog = storage_path('logs/scheduler.log');

Schedule::command('reminders:send')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->appendOutputTo($schedulerLog);

// Monthly opportunity digest — 9am Central on the 1st of each month.
// Volunteers only receive this if opportunity_alerts_enabled is on (admin
// toggle on the Reminders page) and they personally opted in at signup.
Schedule::command('opportunities:send-alerts')
    ->monthlyOn(1, '09:00')
    ->withoutOverlapping()
    ->appendOutputTo($schedulerLog);

// Daily digest of pending volunteers — 8am Central. Skipped when nobody
// is pending, so admins only get email on days that need their attention.
Schedule::command('volunteers:send-pending-digest')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo($schedulerLog);
