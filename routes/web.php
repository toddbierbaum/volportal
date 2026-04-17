<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MagicLinkController;
use App\Http\Controllers\VolunteerDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalendarController::class, 'index'])->name('calendar');
Route::get('/calendar-events', [CalendarController::class, 'events'])->name('calendar.events');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::view('/signup', 'signup')->name('signup');
Route::view('/login-link', 'login-link')->name('login-link');

Route::get('/magic-link/{user}', [MagicLinkController::class, 'login'])
    ->middleware('signed')
    ->name('magic-link.login');

Route::get('/my', [VolunteerDashboardController::class, 'index'])->name('volunteer.dashboard');

Route::post('/logout', function () {
    auth()->guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('calendar');
})->name('logout');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
