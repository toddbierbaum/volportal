<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class MagicLinkController extends Controller
{
    public function login(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            abort(403, 'Admins must use password login.');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Cookie::queue(cookie()->forever('volunteer_id', (string) $user->id));

        return redirect()->route('volunteer.dashboard');
    }

    public function preferences(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            abort(403);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Cookie::queue(cookie()->forever('volunteer_id', (string) $user->id));

        return redirect(route('volunteer.dashboard') . '#preferences')
            ->with('preferences_open', true);
    }
}
