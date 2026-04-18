<?php

namespace App\Http\Controllers;

use App\Models\Signup;

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
}
