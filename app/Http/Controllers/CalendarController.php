<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        if ($redirect = $this->enforceGate()) return $redirect;
        return view('calendar');
    }

    public function events(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($redirect = $this->enforceGate()) return $redirect;

        $start = $request->input('start');
        $end = $request->input('end');

        $events = Event::query()
            ->where('is_published', true)
            ->when($start, fn ($q) => $q->where('ends_at', '>=', $start))
            ->when($end, fn ($q) => $q->where('starts_at', '<=', $end))
            ->with([
                'template',
                'positions' => fn ($q) => $q->where('is_public', true),
                'positions.signups',
            ])
            ->orderBy('starts_at')
            ->get();

        $payload = $events->map(function (Event $event) {
            $totalSlots = $event->positions->sum('slots_needed');
            $filledSlots = $event->positions->sum(
                fn ($p) => $p->signups->where('status', 'confirmed')->count()
            );

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->starts_at->toIso8601String(),
                'end' => $event->ends_at->toIso8601String(),
                'url' => route('events.show', $event->slug),
                'backgroundColor' => $event->template?->color ?? '#6B7280',
                'borderColor' => $event->template?->color ?? '#6B7280',
                'extendedProps' => [
                    'location' => $event->location,
                    'eventType' => $event->template?->name,
                    'slotsTotal' => $totalSlots,
                    'slotsFilled' => $filledSlots,
                    'slotsOpen' => max(0, $totalSlots - $filledSlots),
                ],
            ];
        });

        return response()->json($payload)
            // Cloudflare was happily caching this endpoint's JSON for
            // hours after content changed. no-store both at the edge
            // and in-browser so admins see fresh data immediately after
            // editing events or running seeders.
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * When the approval gate is on, the public calendar is hidden from
     * anon visitors. Logged-in users (volunteer or admin) still see it.
     * Returns a redirect response if the visitor should be bounced.
     */
    private function enforceGate(): ?\Illuminate\Http\RedirectResponse
    {
        $gateOn = (bool) Setting::get('require_approval_before_opportunities', false);
        if (! $gateOn) return null;
        if (auth()->check()) return null;
        return redirect()->route('signup');
    }
}
