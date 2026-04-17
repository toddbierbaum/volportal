<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index'])->name('calendar');
Route::get('/calendar-events', [CalendarController::class, 'events'])->name('calendar.events');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::view('/signup', 'signup')->name('signup');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
