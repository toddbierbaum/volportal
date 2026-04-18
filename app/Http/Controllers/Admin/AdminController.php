<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('name')->get();
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
        ]);

        $admin = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.admins.show', $admin)
            ->with('status', "Admin {$admin->name} added.");
    }

    public function show(User $admin)
    {
        abort_unless($admin->role === 'admin', 404);
        return view('admin.admins.show', ['admin' => $admin]);
    }

    public function destroy(User $admin, Request $request)
    {
        abort_unless($admin->role === 'admin', 404);

        if ($admin->id === $request->user()->id) {
            return redirect()->route('admin.admins.index')
                ->with('status', "Can't delete your own admin account — ask another admin to do it.");
        }

        if (User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.admins.index')
                ->with('status', "Can't delete the only admin — add another admin first.");
        }

        $name = $admin->name;
        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('status', "Deleted admin {$name}.");
    }

    public function resetPassword(User $admin, Request $request)
    {
        abort_unless($admin->role === 'admin', 404);

        if ($admin->id === $request->user()->id) {
            return redirect()->route('admin.admins.show', $admin)
                ->with('status', 'Change your own password from the Profile page instead.');
        }

        $newPassword = Str::random(14);
        $admin->update([
            'password' => Hash::make($newPassword),
        ]);

        return redirect()->route('admin.admins.show', $admin)
            ->with('status', "Password reset. Temporary password: {$newPassword} — share it with {$admin->name} securely, then have them change it from their Profile page.");
    }
}
