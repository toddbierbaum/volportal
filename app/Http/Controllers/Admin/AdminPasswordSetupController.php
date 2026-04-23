<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminPasswordSetupController extends Controller
{
    public function show(User $admin)
    {
        abort_unless($admin->role === 'admin', 403);

        Auth::login($admin);
        request()->session()->regenerate();

        return view('admin.password-setup', ['admin' => $admin]);
    }

    public function store(User $admin, Request $request)
    {
        abort_unless($admin->id === $request->user()?->id, 403);

        $data = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Password set. Welcome!');
    }
}
