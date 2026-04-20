<?php

use App\Http\Controllers\Admin\AdminController as AdminAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\EventTemplateController as AdminEventTemplateController;
use App\Http\Controllers\Admin\VolunteerController as AdminVolunteerController;
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
Route::post('/my/preferences', [VolunteerDashboardController::class, 'updatePreferences'])->name('volunteer.preferences');

Route::post('/logout', function () {
    auth()->guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('calendar');
})->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', AdminEventController::class)->except(['show']);
    Route::post('events/{event}/duplicate', [AdminEventController::class, 'duplicate'])->name('events.duplicate');

    Route::get('/volunteers', [AdminVolunteerController::class, 'index'])->name('volunteers.index');
    Route::get('/volunteers/export', [AdminVolunteerController::class, 'exportCsv'])->name('volunteers.export');
    Route::get('/volunteers/create', [AdminVolunteerController::class, 'create'])->name('volunteers.create');
    Route::post('/volunteers', [AdminVolunteerController::class, 'store'])->name('volunteers.store');
    Route::get('/volunteers/{volunteer}', [AdminVolunteerController::class, 'show'])->name('volunteers.show');
    Route::patch('/volunteers/{volunteer}', [AdminVolunteerController::class, 'update'])->name('volunteers.update');
    Route::delete('/volunteers/{volunteer}', [AdminVolunteerController::class, 'destroy'])->name('volunteers.destroy');

    Route::resource('event-templates', AdminEventTemplateController::class)->except(['show']);

    Route::get('/admins', [AdminAdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [AdminAdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}', [AdminAdminController::class, 'show'])->name('admins.show');
    Route::patch('/admins/{admin}', [AdminAdminController::class, 'update'])->name('admins.update');
    Route::delete('/admins/{admin}', [AdminAdminController::class, 'destroy'])->name('admins.destroy');
    Route::post('/admins/{admin}/reset-password', [AdminAdminController::class, 'resetPassword'])->name('admins.reset-password');

    Route::view('/categories', 'admin.categories')->name('categories');
    Route::view('/notification-schedules', 'admin.notification-schedules')->name('notification-schedules');
});

Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
