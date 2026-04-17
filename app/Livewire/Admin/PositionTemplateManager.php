<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\PositionTemplate;
use Illuminate\Support\Collection;
use Livewire\Component;

class PositionTemplateManager extends Component
{
    public string $title = '';
    public ?int $categoryId = null;
    public ?int $defaultDurationMinutes = 180;
    public string $description = '';

    public ?int $editingId = null;
    public string $flash = '';

    public function add(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'defaultDurationMinutes' => 'nullable|integer|min:1|max:1440',
            'description' => 'nullable|string',
        ]);

        PositionTemplate::create([
            'title' => $data['title'],
            'category_id' => $data['categoryId'],
            'default_duration_minutes' => $data['defaultDurationMinutes'],
            'description' => $data['description'] ?: null,
        ]);

        $this->resetForm();
        $this->flash = 'Template added.';
    }

    public function startEdit(int $id): void
    {
        $t = PositionTemplate::find($id);
        if (! $t) return;
        $this->editingId = $id;
        $this->title = $t->title;
        $this->categoryId = $t->category_id;
        $this->defaultDurationMinutes = $t->default_duration_minutes;
        $this->description = (string) $t->description;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'categoryId' => 'required|exists:categories,id',
            'defaultDurationMinutes' => 'nullable|integer|min:1|max:1440',
            'description' => 'nullable|string',
        ]);

        $t = PositionTemplate::find($this->editingId);
        if (! $t) return;

        $t->update([
            'title' => $data['title'],
            'category_id' => $data['categoryId'],
            'default_duration_minutes' => $data['defaultDurationMinutes'],
            'description' => $data['description'] ?: null,
        ]);

        $this->resetForm();
        $this->flash = 'Template updated.';
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $t = PositionTemplate::find($id);
        if (! $t) return;

        $t->delete();
        $this->flash = "Template \"{$t->title}\" deleted. Existing positions keep working (template link becomes null).";
    }

    public function getItemsProperty(): Collection
    {
        return PositionTemplate::with('category')->orderBy('title')->withCount('positions')->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    private function resetForm(): void
    {
        $this->reset(['title', 'description', 'editingId', 'categoryId']);
        $this->defaultDurationMinutes = 180;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.position-template-manager', [
            'items' => $this->items,
            'categories' => $this->categories,
        ]);
    }
}
