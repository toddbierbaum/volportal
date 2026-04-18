<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\NotificationSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class EventWizard extends Component
{
    public ?int $eventTemplateId = null;
    public string $title = '';
    public string $description = '';
    public string $startsAt = '';
    public string $endsAt = '';
    public string $location = '';
    public bool $isPublished = false;

    /** @var array<int, array> each entry keys: categoryId, title, slotsNeeded, isPublic, startsAt, endsAt */
    public array $draftPositions = [];

    /** @var array<int, array> each entry keys: offsetMinutes, channel, label */
    public array $draftSchedules = [];

    public ?int $categoryId = null;
    public string $positionTitle = '';
    public int $positionSlots = 1;
    public bool $positionIsPublic = true;
    public string $positionStartsAt = '';
    public string $positionEndsAt = '';

    public ?int $editingIndex = null;

    public function mount(): void
    {
        $this->startsAt = now()->addWeek()->setTime(18, 0)->format('Y-m-d\TH:i');
        $this->endsAt = now()->addWeek()->setTime(20, 30)->format('Y-m-d\TH:i');
        $this->syncPositionTimesFromEvent();
    }

    public function updatedStartsAt(): void
    {
        $this->syncPositionTimesFromEvent();
        $this->refreshDraftPositionTimes();
    }

    public function updatedEndsAt(): void
    {
        $this->syncPositionTimesFromEvent();
        $this->refreshDraftPositionTimes();
    }

    public function updatedEventTemplateId($value): void
    {
        if (! $value) {
            $this->draftPositions = [];
            $this->draftSchedules = [];
            return;
        }
        $template = EventTemplate::with(['positions', 'schedules'])->find($value);
        if (! $template) return;

        // Don't clobber user-added draft positions if they've already added some.
        if (empty($this->draftPositions)) {
            $this->draftPositions = $this->positionsFromTemplate($template);
        }

        if (empty($this->draftSchedules)) {
            $this->draftSchedules = $template->schedules->map(fn ($s) => [
                'offsetMinutes' => $s->offset_minutes,
                'channel' => $s->channel,
                'label' => $s->label,
            ])->all();
        }
    }

    private function positionsFromTemplate(EventTemplate $template): array
    {
        $eventStart = Carbon::parse($this->startsAt);
        return $template->positions->map(function ($p) use ($eventStart) {
            $start = $eventStart->copy()->subMinutes($p->call_offset_minutes);
            $end = $start->copy()->addMinutes($p->duration_minutes);
            return [
                'categoryId' => $p->category_id,
                'title' => $p->title,
                'slotsNeeded' => $p->slots_needed,
                'isPublic' => (bool) $p->is_public,
                'startsAt' => $start->format('Y-m-d\TH:i'),
                'endsAt' => $end->format('Y-m-d\TH:i'),
            ];
        })->all();
    }

    private function refreshDraftPositionTimes(): void
    {
        if (! $this->eventTemplateId || empty($this->draftPositions)) return;
        $template = EventTemplate::with('positions')->find($this->eventTemplateId);
        if (! $template) return;
        // Only re-derive positions that came from the template; leave
        // admin-added custom ones alone.
        $this->draftPositions = $this->positionsFromTemplate($template);
    }

    public function addDraftPosition(): void
    {
        $data = $this->validate([
            'positionTitle' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'positionSlots' => 'required|integer|min:1|max:50',
            'positionStartsAt' => 'required|date',
            'positionEndsAt' => 'required|date|after_or_equal:positionStartsAt',
        ]);

        $this->draftPositions[] = [
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'slotsNeeded' => $data['positionSlots'],
            'isPublic' => $this->positionIsPublic,
            'startsAt' => $data['positionStartsAt'],
            'endsAt' => $data['positionEndsAt'],
        ];

        $this->resetPositionForm();
    }

    public function editDraftPosition(int $index): void
    {
        if (! isset($this->draftPositions[$index])) return;
        $p = $this->draftPositions[$index];
        $this->editingIndex = $index;
        $this->categoryId = $p['categoryId'];
        $this->positionTitle = $p['title'];
        $this->positionSlots = $p['slotsNeeded'];
        $this->positionIsPublic = $p['isPublic'] ?? true;
        $this->positionStartsAt = $p['startsAt'];
        $this->positionEndsAt = $p['endsAt'];
        $this->resetValidation();
    }

    public function saveEditedPosition(): void
    {
        if ($this->editingIndex === null) return;

        $data = $this->validate([
            'positionTitle' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'positionSlots' => 'required|integer|min:1|max:50',
            'positionStartsAt' => 'required|date',
            'positionEndsAt' => 'required|date|after_or_equal:positionStartsAt',
        ]);

        $this->draftPositions[$this->editingIndex] = [
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'slotsNeeded' => $data['positionSlots'],
            'isPublic' => $this->positionIsPublic,
            'startsAt' => $data['positionStartsAt'],
            'endsAt' => $data['positionEndsAt'],
        ];

        $this->resetPositionForm();
    }

    public function cancelEditDraftPosition(): void
    {
        $this->resetPositionForm();
    }

    public function removeDraftPosition(int $index): void
    {
        unset($this->draftPositions[$index]);
        $this->draftPositions = array_values($this->draftPositions);
    }

    public function save()
    {
        $eventData = $this->validate([
            'eventTemplateId' => 'nullable|exists:event_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date|after_or_equal:startsAt',
            'location' => 'nullable|string|max:255',
            'isPublished' => 'boolean',
        ]);

        $event = DB::transaction(function () use ($eventData) {
            $event = Event::create([
                'event_template_id' => $eventData['eventTemplateId'],
                'title' => $eventData['title'],
                'slug' => $this->uniqueSlug($eventData['title']),
                'description' => $eventData['description'] ?: null,
                'starts_at' => $eventData['startsAt'],
                'ends_at' => $eventData['endsAt'],
                'location' => $eventData['location'] ?: null,
                'is_published' => $eventData['isPublished'],
            ]);

            foreach ($this->draftPositions as $draft) {
                $event->positions()->create([
                    'category_id' => $draft['categoryId'],
                    'title' => $draft['title'],
                    'slots_needed' => $draft['slotsNeeded'],
                    'is_public' => $draft['isPublic'] ?? true,
                    'starts_at' => $draft['startsAt'],
                    'ends_at' => $draft['endsAt'],
                ]);
            }

            foreach ($this->draftSchedules as $draft) {
                NotificationSchedule::create([
                    'event_id' => $event->id,
                    'offset_minutes' => $draft['offsetMinutes'],
                ]);
            }

            return $event;
        });

        $msg = "Event \"{$event->title}\" created";
        if (count($this->draftPositions) > 0) {
            $msg .= ' with ' . count($this->draftPositions) . ' position' . (count($this->draftPositions) === 1 ? '' : 's');
        }
        if (count($this->draftSchedules) > 0) {
            $msg .= ' and ' . count($this->draftSchedules) . ' reminder' . (count($this->draftSchedules) === 1 ? '' : 's');
        }
        $msg .= '.';
        session()->flash('status', $msg);

        return redirect()->route('admin.events.edit', $event);
    }

    public function getTemplatesProperty(): Collection
    {
        return EventTemplate::orderBy('name')->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    private function syncPositionTimesFromEvent(): void
    {
        if (! $this->positionStartsAt && $this->startsAt) {
            $this->positionStartsAt = $this->startsAt;
        }
        if (! $this->positionEndsAt && $this->endsAt) {
            $this->positionEndsAt = $this->endsAt;
        }
    }

    private function resetPositionForm(): void
    {
        $this->reset(['categoryId', 'positionTitle', 'editingIndex']);
        $this->positionSlots = 1;
        $this->positionIsPublic = true;
        $this->positionStartsAt = $this->startsAt;
        $this->positionEndsAt = $this->endsAt;
        $this->resetValidation();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $n = 2;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.event-wizard', [
            'templates' => $this->templates,
            'categories' => $this->categories,
        ]);
    }
}
