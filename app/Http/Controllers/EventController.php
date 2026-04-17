<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function show(Event $event)
    {
        abort_unless($event->is_published, 404);

        $event->load([
            'type',
            'positions' => fn ($q) => $q->where('is_public', true),
            'positions.category',
            'positions.signups',
        ]);

        return view('events.show', ['event' => $event]);
    }
}
