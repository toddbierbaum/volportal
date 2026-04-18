<?php

namespace App\Http\Controllers;

use App\Models\Signup;
use Illuminate\Http\Request;

class VolunteerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login-link');
        }

        if ($user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        $upcomingSignups = Signup::with(['position.event.template', 'position.category'])
            ->where('user_id', $user->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '>=', now()))
            ->whereIn('status', ['confirmed', 'waitlisted'])
            ->get()
            ->sortBy(fn ($s) => $s->position->event->starts_at)
            ->values();

        $pastSignups = Signup::with(['position.event'])
            ->where('user_id', $user->id)
            ->whereHas('position.event', fn ($q) => $q->where('starts_at', '<', now()))
            ->get()
            ->sortByDesc(fn ($s) => $s->position->event->starts_at)
            ->values();

        return view('volunteer.dashboard', [
            'user' => $user->loadMissing('categories'),
            'upcomingSignups' => $upcomingSignups,
            'pastSignups' => $pastSignups,
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ! $user->isAdmin(), 403);

        $data = $request->validate([
            'phone' => 'nullable|string|max:30',
            'sms_opt_in' => 'sometimes|boolean',
        ]);

        $smsOptIn = (bool) ($data['sms_opt_in'] ?? false);
        if ($smsOptIn && empty($data['phone'] ?? null)) {
            return back()->withErrors(['phone' => 'A phone number is required to receive text reminders.']);
        }

        $user->update([
            'phone' => $data['phone'] ?? null,
            'sms_opt_in' => $smsOptIn,
        ]);

        return redirect()->route('volunteer.dashboard')
            ->with('status', 'Preferences updated.');
    }
}
