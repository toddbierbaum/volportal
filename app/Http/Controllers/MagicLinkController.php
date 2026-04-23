<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class MagicLinkController extends Controller
{
    public function login(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            abort(403, 'Admins must use password login.');
        }

        $this->invalidateToken($request);

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

        $this->invalidateToken($request);

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Cookie::queue(cookie()->forever('volunteer_id', (string) $user->id));

        return redirect(route('volunteer.dashboard') . '#preferences')
            ->with('preferences_open', true);
    }

    private function invalidateToken(Request $request): void
    {
        $hash = hash('sha256', (string) $request->query('signature'));
        $inserted = DB::table('used_magic_link_tokens')->insertOrIgnore([
            'token_hash' => $hash,
            'created_at' => now(),
        ]);
        abort_if($inserted === 0, 403, 'This link has already been used.');
    }
}
