<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Signup;
use App\Models\User;

class DashboardController extends Controller
{
    // Keep the dashboard focused on actionable near-term work. 60 days is
    // long enough to plan (e.g. summer kids shows from late spring) but
    // short enough that the open-slots number doesn't feel like a fire
    // drill when really it's just the full season showing at once.
    private const HORIZON_DAYS = 60;

    public function index()
    {
        $horizon = now()->addDays(self::HORIZON_DAYS);

        $upcomingEvents = Event::query()
            ->with(['template', 'positions.signups'])
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', $horizon)
            ->orderBy('starts_at')
            ->take(10)
            ->get();

        $stats = [
            'upcoming_events' => Event::where('starts_at', '>=', now())
                ->where('starts_at', '<=', $horizon)
                ->count(),
            'volunteers' => User::where('role', 'volunteer')->count(),
            'confirmed_signups' => Signup::where('status', 'confirmed')
                ->whereHas('position.event', fn ($q) => $q
                    ->where('starts_at', '>=', now())
                    ->where('starts_at', '<=', $horizon))
                ->count(),
            'open_slots' => $this->openSlotsInHorizon($horizon),
            'pending_review' => User::where('role', 'volunteer')->whereNull('approved_at')->count(),
        ];

        return view('admin.dashboard', [
            'upcomingEvents' => $upcomingEvents,
            'stats' => $stats,
            'horizonDays' => self::HORIZON_DAYS,
        ]);
    }

    private function openSlotsInHorizon($horizon): int
    {
        $events = Event::with('positions.signups')
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', $horizon)
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
