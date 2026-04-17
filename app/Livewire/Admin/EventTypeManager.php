<?php

namespace App\Livewire\Admin;

use App\Models\EventType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class EventTypeManager extends Component
{
    public string $name = '';
    public string $color = '#4F46E5';

    public ?int $editingId = null;
    public string $flash = '';

    public function add(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        EventType::create($data + ['slug' => $this->uniqueSlug($data['name'])]);
        $this->resetForm();
        $this->flash = 'Event type added.';
    }

    public function startEdit(int $id): void
    {
        $t = EventType::find($id);
        if (! $t) return;
        $this->editingId = $id;
        $this->name = $t->name;
        $this->color = $t->color ?? '#4F46E5';
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $t = EventType::find($this->editingId);
        if (! $t) return;

        if ($data['name'] !== $t->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $t->id);
        }
        $t->update($data);
        $this->resetForm();
        $this->flash = 'Event type updated.';
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $t = EventType::find($id);
        if (! $t) return;

        $t->delete();
        $this->flash = "Event type \"{$t->name}\" deleted. Existing events keep working (type becomes null).";
    }

    public function getItemsProperty(): Collection
    {
        return EventType::orderBy('name')->withCount('events')->get();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
        $this->color = '#4F46E5';
        $this->resetValidation();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;
        while (EventType::where('slug', $slug)->where('id', '!=', $ignoreId ?? 0)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.event-type-manager', ['items' => $this->items]);
    }
}
