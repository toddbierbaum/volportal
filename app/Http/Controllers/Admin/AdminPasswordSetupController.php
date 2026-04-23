<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminPasswordSetupController extends Controller
{
    public function show(Request $request, User $admin)
    {
        $this->invalidateToken($request);

        abort_unless($admin->role === 'admin', 403);

        Auth::login($admin);
        $request->session()->regenerate();

        return view('admin.password-setup', ['admin' => $admin]);
    }

    public function store(User $admin, Request $request)
    {
        abort_unless($admin->id === $request->user()?->id, 403);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return view('admin.password-setup', ['admin' => $admin])
                ->withErrors($validator);
        }

        $admin->update(['password' => Hash::make($validator->validated()['password'])]);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Password set. Welcome!');
    }

    private function invalidateToken(Request $request): void
    {
        $hash = hash('sha256', (string) $request->query('signature'));
        $inserted = DB::table('used_admin_setup_tokens')->insertOrIgnore([
            'token_hash' => $hash,
            'created_at' => now(),
        ]);
        abort_if($inserted === 0, 403, 'This link has already been used.');
    }
}
