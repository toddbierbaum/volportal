<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Signup;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::query()
            ->with(['template', 'positions.signups'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(10)
            ->get();

        $stats = [
            'upcoming_events' => Event::where('starts_at', '>=', now())->count(),
            'volunteers' => User::where('role', 'volunteer')->count(),
            'confirmed_signups' => Signup::where('status', 'confirmed')
                ->whereHas('position.event', fn ($q) => $q->where('starts_at', '>=', now()))
                ->count(),
            'open_slots' => $this->openSlotsAcrossUpcomingEvents(),
            'pending_review' => User::where('role', 'volunteer')->whereNull('approved_at')->count(),
        ];

        return view('admin.dashboard', compact('upcomingEvents', 'stats'));
    }

    private function openSlotsAcrossUpcomingEvents(): int
    {
        $events = Event::with('positions.signups')
            ->where('starts_at', '>=', now())
            ->get();

        $open = 0;
        foreach ($events as $event) {
            foreach ($event->positions as $position) {
                $filled = $position->signups->where('status', 'confirmed')->count();
                $open += max(0, $position->slots_needed - $filled);
            }
        }

        return $open;
    }
}
