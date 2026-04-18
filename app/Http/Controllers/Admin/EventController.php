<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        $upcoming = Event::with('template', 'positions.signups')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->get();

        $past = Event::with('template')
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at')
            ->take(25)
            ->get();

        return view('admin.events.index', compact('upcoming', 'past'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', [
            'event' => $event->loadMissing('template'),
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validateData($request, $event);
        if ($data['title'] !== $event->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $event->id);
        }
        $event->update($data);

        return redirect()->route('admin.events.edit', $event)
            ->with('status', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('status', 'Event deleted.');
    }

    public function duplicate(Event $event)
    {
        $copy = $event->replicate();
        $copy->title = $event->title . ' (copy)';
        $copy->slug = $this->uniqueSlug($copy->title);
        $copy->is_published = false;
        $copy->starts_at = $event->starts_at->copy()->addWeek();
        $copy->ends_at = $event->ends_at->copy()->addWeek();
        $copy->save();

        foreach ($event->positions as $position) {
            $copy->positions()->create([
                'category_id' => $position->category_id,
                'title' => $position->title,
                'description' => $position->description,
                'slots_needed' => $position->slots_needed,
                'is_public' => $position->is_public,
                'starts_at' => $position->starts_at->copy()->addWeek(),
                'ends_at' => $position->ends_at->copy()->addWeek(),
            ]);
        }

        return redirect()->route('admin.events.edit', $copy)
            ->with('status', 'Event duplicated. Adjust the date and publish when ready.');
    }

    private function validateData(Request $request, ?Event $event = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'is_published' => 'sometimes|boolean',
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 2;
        while (Event::where('slug', $slug)->where('id', '!=', $ignoreId ?? 0)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
