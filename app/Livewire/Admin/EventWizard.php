<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventType;
use App\Models\PositionTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class EventWizard extends Component
{
    public ?int $eventTypeId = null;
    public string $title = '';
    public string $description = '';
    public string $startsAt = '';
    public string $endsAt = '';
    public string $location = '';
    public bool $isPublished = false;

    /** @var array<int, array> each entry keys: templateId?, categoryId, title, slotsNeeded, startsAt, endsAt */
    public array $draftPositions = [];

    public ?int $templateId = null;
    public ?int $categoryId = null;
    public string $positionTitle = '';
    public int $positionSlots = 1;
    public string $positionStartsAt = '';
    public string $positionEndsAt = '';

    public ?int $editingIndex = null;

    public function mount(): void
    {
        $this->startsAt = now()->addWeek()->setTime(18, 0)->format('Y-m-d\TH:i');
        $this->endsAt = now()->addWeek()->setTime(20, 30)->format('Y-m-d\TH:i');
        $this->syncPositionTimesFromEvent();
    }

    public function updatedStartsAt(): void { $this->syncPositionTimesFromEvent(); }
    public function updatedEndsAt(): void   { $this->syncPositionTimesFromEvent(); }

    public function updatedTemplateId($value): void
    {
        if (! $value) return;
        $template = PositionTemplate::find($value);
        if (! $template) return;

        $this->positionTitle = $template->title;
        $this->categoryId = $template->category_id;

        if ($template->default_duration_minutes && $this->startsAt) {
            $start = \Carbon\Carbon::parse($this->startsAt);
            $this->positionStartsAt = $start->format('Y-m-d\TH:i');
            $this->positionEndsAt = $start->copy()->addMinutes($template->default_duration_minutes)->format('Y-m-d\TH:i');
        }
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
            'templateId' => $this->templateId,
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'slotsNeeded' => $data['positionSlots'],
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
        $this->templateId = $p['templateId'];
        $this->categoryId = $p['categoryId'];
        $this->positionTitle = $p['title'];
        $this->positionSlots = $p['slotsNeeded'];
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
            'templateId' => $this->templateId,
            'categoryId' => $data['categoryId'],
            'title' => $data['positionTitle'],
            'slotsNeeded' => $data['positionSlots'],
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
            'eventTypeId' => 'nullable|exists:event_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date|after_or_equal:startsAt',
            'location' => 'nullable|string|max:255',
            'isPublished' => 'boolean',
        ]);

        $event = DB::transaction(function () use ($eventData) {
            $event = Event::create([
                'event_type_id' => $eventData['eventTypeId'],
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
                    'position_template_id' => $draft['templateId'],
                    'category_id' => $draft['categoryId'],
                    'title' => $draft['title'],
                    'slots_needed' => $draft['slotsNeeded'],
                    'starts_at' => $draft['startsAt'],
                    'ends_at' => $draft['endsAt'],
                ]);
            }

            return $event;
        });

        session()->flash('status', count($this->draftPositions) > 0
            ? "Event \"{$event->title}\" created with " . count($this->draftPositions) . ' position' . (count($this->draftPositions) === 1 ? '' : 's') . '.'
            : "Event \"{$event->title}\" created. Add positions below.");

        return redirect()->route('admin.events.edit', $event);
    }

    public function getEventTypesProperty(): Collection
    {
        return EventType::orderBy('name')->get();
    }

    public function getTemplatesProperty(): Collection
    {
        return PositionTemplate::with('category')->orderBy('title')->get();
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
        $this->reset(['templateId', 'categoryId', 'positionTitle', 'editingIndex']);
        $this->positionSlots = 1;
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
            'eventTypes' => $this->eventTypes,
            'templates' => $this->templates,
            'categories' => $this->categories,
        ]);
    }
}
