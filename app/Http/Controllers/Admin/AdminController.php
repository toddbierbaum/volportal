<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordSetupMail;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8',
        ]);

        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;
        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.'])->withInput();
        }

        $admin = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $e164 ?: $rawPhone,
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

    public function update(User $admin, Request $request)
    {
        abort_unless($admin->role === 'admin', 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => 'nullable|string|max:30',
        ]);

        $rawPhone = $data['phone'] ?? null;
        $e164 = $rawPhone ? SmsSender::toE164($rawPhone) : null;
        if ($rawPhone && ! $e164) {
            return back()->withErrors(['phone' => 'Phone must be a US number with 10 digits — e.g. (850) 555-1234.'])->withInput();
        }

        $admin->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $e164 ?: $rawPhone,
        ]);

        return redirect()->route('admin.admins.show', $admin)
            ->with('status', "Saved {$admin->name}.");
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

        Mail::to($admin->email)->send(new AdminPasswordSetupMail($admin));

        return redirect()->route('admin.admins.show', $admin)
            ->with('status', "Password setup link sent to {$admin->email}. The link expires in 24 hours.");
    }
}
