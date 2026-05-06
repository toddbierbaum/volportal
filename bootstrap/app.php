<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureAdminTotpVerified;
use App\Support\LockoutLogger;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'totp' => EnsureAdminTotpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $ref = LockoutLogger::log('middleware:'.($request->route()?->getName() ?? 'unknown'));

            return response()->view('errors.429', ['errorRef' => $ref], 429);
        });
    })->create();
