<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Admin\AdminTotpController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTotpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // TOTP is optional for admins — un-enrolled admins pass straight through
        // (a banner nudges them to set it up). Only enrolled admins are challenged.
        if (! $user?->isAdmin() || ! $user->hasTotpEnabled()) {
            return $next($request);
        }

        if (! AdminTotpController::isTotpFresh($request->session()->get('totp_verified_at'))) {
            return redirect()->route('admin.totp.challenge');
        }

        return $next($request);
    }
}
