<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar');
    }

    public function events(Request $request): JsonResponse
    {
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
}
