<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventTemplateController extends Controller
{
    public function index()
    {
        $templates = EventTemplate::withCount(['positions', 'schedules', 'events'])
            ->orderBy('name')
            ->get();

        return view('admin.event-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.event-templates.create', [
            'template' => new EventTemplate(['color' => '#4F46E5']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);
        $template = EventTemplate::create($data);

        return redirect()->route('admin.event-templates.edit', $template)
            ->with('status', "Template \"{$template->name}\" created. Add default positions and reminders below.");
    }

    public function edit(EventTemplate $eventTemplate)
    {
        return view('admin.event-templates.edit', ['template' => $eventTemplate]);
    }

    public function update(Request $request, EventTemplate $eventTemplate)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
        ]);

        if ($data['name'] !== $eventTemplate->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $eventTemplate->id);
        }
        $eventTemplate->update($data);

        return redirect()->route('admin.event-templates.edit', $eventTemplate)
            ->with('status', 'Template updated.');
    }

    public function destroy(EventTemplate $eventTemplate)
    {
        if ($eventTemplate->events()->count() > 0) {
            return redirect()->route('admin.event-templates.index')
                ->with('status', "Can't delete \"{$eventTemplate->name}\" — {$eventTemplate->events()->count()} event(s) still use it. Reassign or delete those events first.");
        }

        $eventTemplate->delete();
        return redirect()->route('admin.event-templates.index')->with('status', 'Template deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;
        while (EventTemplate::where('slug', $slug)->where('id', '!=', $ignoreId ?? 0)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }
}
