<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTotpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            return $next($request);
        }

        if (! $user->hasTotpEnabled()) {
            return redirect()->route('admin.totp.enroll');
        }

        if (! $request->session()->get('totp_verified')) {
            return redirect()->route('admin.totp.challenge');
        }

        return $next($request);
    }
}
