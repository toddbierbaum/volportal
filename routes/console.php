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
