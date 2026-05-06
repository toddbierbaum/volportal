<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->middleware(['throttle:password.email'])
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->middleware(['throttle:password.reset.form'])
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
