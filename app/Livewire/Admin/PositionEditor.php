<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\Position;
use App\Models\PositionTemplate;
use Illuminate\Support\Collection;
use Livewire\Component;

class PositionEditor extends Component
{
    public Event $event;

    public ?int $templateId = null;
    public ?int $categoryId = null;
    public string $title = '';
    public int $slotsNeeded = 1;
    public bool $isPublic = true;
    public string $startsAt = '';
    public string $endsAt = '';

    public ?int $editingPositionId = null;

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->startsAt = $event->starts_at->format('Y-m-d\TH:i');
        $this->endsAt = $event->ends_at->format('Y-m-d\TH:i');
    }

    public function updatedTemplateId($value): void
    {
        if (! $value) return;
        $template = PositionTemplate::find($value);
        if (! $template) return;

        $this->title = $template->title;
        $this->categoryId = $template->category_id;

        if ($template->default_duration_minutes) {
            $start = $this->event->starts_at->copy();
            $this->startsAt = $start->format('Y-m-d\TH:i');
            $this->endsAt = $start->copy()->addMinutes($template->default_duration_minutes)->format('Y-m-d\TH:i');
        }
    }

    public function addPosition(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date|after_or_equal:startsAt',
        ]);

        $this->event->positions()->create([
            'position_template_id' => $this->templateId,
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'starts_at' => $data['startsAt'],
            'ends_at' => $data['endsAt'],
        ]);

        $this->resetForm();
    }

    public function startEdit(int $positionId): void
    {
        $position = $this->event->positions()->find($positionId);
        if (! $position) return;

        $this->editingPositionId = $position->id;
        $this->templateId = $position->position_template_id;
        $this->categoryId = $position->category_id;
        $this->title = $position->title;
        $this->slotsNeeded = $position->slots_needed;
        $this->isPublic = (bool) $position->is_public;
        $this->startsAt = $position->starts_at->format('Y-m-d\TH:i');
        $this->endsAt = $position->ends_at->format('Y-m-d\TH:i');
    }

    public function saveEdit(): void
    {
        $position = $this->event->positions()->find($this->editingPositionId);
        if (! $position) return;

        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'startsAt' => 'required|date',
            'endsAt' => 'required|date|after_or_equal:startsAt',
        ]);

        $position->update([
            'position_template_id' => $this->templateId,
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'starts_at' => $data['startsAt'],
            'ends_at' => $data['endsAt'],
        ]);

        $this->resetForm();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function removePosition(int $positionId): void
    {
        Position::where('id', $positionId)
            ->where('event_id', $this->event->id)
            ->delete();
    }

    private function resetForm(): void
    {
        $this->reset(['templateId', 'categoryId', 'title', 'editingPositionId']);
        $this->slotsNeeded = 1;
        $this->isPublic = true;
        $this->startsAt = $this->event->starts_at->format('Y-m-d\TH:i');
        $this->endsAt = $this->event->ends_at->format('Y-m-d\TH:i');
        $this->resetValidation();
    }

    public function getTemplatesProperty(): Collection
    {
        return PositionTemplate::with('category')->orderBy('title')->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function getPositionsProperty(): Collection
    {
        return $this->event->positions()
            ->with(['category', 'signups'])
            ->orderBy('starts_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.position-editor', [
            'templates' => $this->templates,
            'categories' => $this->categories,
            'positions' => $this->positions,
        ]);
    }
}
