<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
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

    /** @var array<int, array> keys: categoryId, title, description, slotsNeeded, isPublic, callOffsetMinutes, durationMinutes */
    public array $draftPositions = [];

    /** @var array<int, array> keys: offsetMinutes, channel, label */
    public array $draftSchedules = [];

    public ?int $categoryId = null;
    public string $positionTitle = '';
    public string $positionDescription = '';
    public int $positionSlots = 1;
    public bool $positionIsPublic = true;
    public int $positionCallOffsetMinutes = 60;
    public int $positionDurationMinutes = 180;

    public ?int $editingIndex = null;
    public bool $showAddForm = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->startsAt = now()->addWeek()->setTime(18, 0)->format('Y-m-d\TH:i');
        $this->endsAt = now()->addWeek()->setTime(20, 30)->format('Y-m-d\TH:i');
    }

    public function startAddDraft(): void
    {
        $this->resetPositionForm();
        $this->showAddForm = true;
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

        if (empty($this->draftPositions)) {
            $this->draftPositions = $template->positions->map(fn ($p) => [
                'categoryId' => $p->category_id,
                'title' => $p->title,
                'description' => (string) $p->description,
                'slotsNeeded' => $p->slots_needed,
                'isPublic' => (bool) $p->is_public,
                'callOffsetMinutes' => $p->call_offset_minutes,
                'durationMinutes' => $p->duration_minutes,
            ])->all();
        }

        if (empty($this->draftSchedules)) {
            $this->draftSchedules = $template->schedules->map(fn ($s) => [
                'offsetMinutes' => $s->offset_minutes,
                'channel' => $s->channel,
                'label' => $s->label,
            ])->all();
        }
    }

    public function addDraftPosition(): void
    {
        $data = $this->validate([
            'positionTitle' => 'required|string|max:255',
            'positionDescription' => 'nullable|string',
            'categoryId' => 'required|exists:categories,id',
            'positionSlots' => 'required|integer|min:1|max:50',
            'positionCallOffsetMinutes' => 'required|integer|min:0|max:1440',
            'positionDurationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        $this->draftPositions[] = [
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'description' => $data['positionDescription'],
            'slotsNeeded' => $data['positionSlots'],
            'isPublic' => $this->positionIsPublic,
            'callOffsetMinutes' => $data['positionCallOffsetMinutes'],
            'durationMinutes' => $data['positionDurationMinutes'],
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
        $this->positionDescription = (string) ($p['description'] ?? '');
        $this->positionSlots = $p['slotsNeeded'];
        $this->positionIsPublic = $p['isPublic'] ?? true;
        $this->positionCallOffsetMinutes = $p['callOffsetMinutes'] ?? 60;
        $this->positionDurationMinutes = $p['durationMinutes'] ?? 180;
        $this->resetValidation();
    }

    public function saveEditedPosition(): void
    {
        if ($this->editingIndex === null) return;

        $data = $this->validate([
            'positionTitle' => 'required|string|max:255',
            'positionDescription' => 'nullable|string',
            'categoryId' => 'required|exists:categories,id',
            'positionSlots' => 'required|integer|min:1|max:50',
            'positionCallOffsetMinutes' => 'required|integer|min:0|max:1440',
            'positionDurationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        $this->draftPositions[$this->editingIndex] = [
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'description' => $data['positionDescription'],
            'slotsNeeded' => $data['positionSlots'],
            'isPublic' => $this->positionIsPublic,
            'callOffsetMinutes' => $data['positionCallOffsetMinutes'],
            'durationMinutes' => $data['positionDurationMinutes'],
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

            $eventStart = Carbon::parse($eventData['startsAt']);
            foreach ($this->draftPositions as $draft) {
                $start = $eventStart->copy()->subMinutes((int) ($draft['callOffsetMinutes'] ?? 60));
                $end = $start->copy()->addMinutes((int) ($draft['durationMinutes'] ?? 180));

                $event->positions()->create([
                    'category_id' => $draft['categoryId'],
                    'title' => $draft['title'],
                    'description' => ($draft['description'] ?? '') ?: null,
                    'slots_needed' => $draft['slotsNeeded'],
                    'is_public' => $draft['isPublic'] ?? true,
                    'starts_at' => $start,
                    'ends_at' => $end,
                ]);
            }

            // Template schedules are read live via $event->template->schedules
            // by the reminder command — not copied to per-event rows.

            return $event;
        });

        $msg = "Event \"{$event->title}\" created";
        if (count($this->draftPositions) > 0) {
            $msg .= ' with ' . count($this->draftPositions) . ' position' . (count($this->draftPositions) === 1 ? '' : 's');
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

    private function resetPositionForm(): void
    {
        $this->reset(['categoryId', 'positionTitle', 'positionDescription', 'editingIndex', 'showAddForm']);
        $this->positionSlots = 1;
        $this->positionIsPublic = true;
        $this->positionCallOffsetMinutes = 60;
        $this->positionDurationMinutes = 180;
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
