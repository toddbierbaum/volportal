<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\EventTemplate;
use App\Models\EventTemplatePosition;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventTemplatePositionEditor extends Component
{
    public EventTemplate $template;

    public ?int $categoryId = null;
    public string $title = '';
    public int $slotsNeeded = 1;
    public bool $isPublic = true;
    public int $callOffsetMinutes = 60;
    public int $durationMinutes = 180;

    public ?int $editingId = null;
    public bool $showAddForm = false;

    public function mount(EventTemplate $template): void
    {
        $this->template = $template;
    }

    public function startAdd(): void
    {
        $this->resetForm();
        $this->showAddForm = true;
    }

    public function add(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'callOffsetMinutes' => 'required|integer|min:0|max:1440',
            'durationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        $nextOrder = ($this->template->positions()->max('position_order') ?? 0) + 10;

        $this->template->positions()->create([
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'call_offset_minutes' => $data['callOffsetMinutes'],
            'duration_minutes' => $data['durationMinutes'],
            'position_order' => $nextOrder,
        ]);

        $this->resetForm();
    }

    public function startEdit(int $id): void
    {
        $p = $this->template->positions()->find($id);
        if (! $p) return;

        $this->editingId = $id;
        $this->categoryId = $p->category_id;
        $this->title = $p->title;
        $this->slotsNeeded = $p->slots_needed;
        $this->isPublic = (bool) $p->is_public;
        $this->callOffsetMinutes = $p->call_offset_minutes;
        $this->durationMinutes = $p->duration_minutes;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'callOffsetMinutes' => 'required|integer|min:0|max:1440',
            'durationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        $p = $this->template->positions()->find($this->editingId);
        if (! $p) return;

        $p->update([
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'call_offset_minutes' => $data['callOffsetMinutes'],
            'duration_minutes' => $data['durationMinutes'],
        ]);

        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        EventTemplatePosition::where('id', $id)
            ->where('event_template_id', $this->template->id)
            ->delete();
    }

    public function getPositionsProperty(): Collection
    {
        return $this->template->positions()->with('category')->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    private function resetForm(): void
    {
        $this->reset(['categoryId', 'title', 'editingId', 'showAddForm']);
        $this->slotsNeeded = 1;
        $this->isPublic = true;
        $this->callOffsetMinutes = 60;
        $this->durationMinutes = 180;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.event-template-position-editor', [
            'positions' => $this->positions,
            'categories' => $this->categories,
        ]);
    }
}
