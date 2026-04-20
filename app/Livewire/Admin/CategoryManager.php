<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\EventTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class CategoryManager extends Component
{
    public string $name = '';
    public string $description = '';
    public string $color = '#4F46E5';
    public bool $requiresAgeCertification = false;
    public ?int $eventTemplateId = null;

    public ?int $editingId = null;
    public string $flash = '';

    public function add(): void
    {
        $data = $this->validatedPayload();

        Category::create($data + ['slug' => $this->uniqueSlug($data['name'])]);
        $this->resetForm();
        $this->flash = 'Category added.';
    }

    public function startEdit(int $id): void
    {
        $c = Category::find($id);
        if (! $c) return;
        $this->editingId = $id;
        $this->name = $c->name;
        $this->description = (string) $c->description;
        $this->color = $c->color ?? '#4F46E5';
        $this->requiresAgeCertification = (bool) $c->requires_age_certification;
        $this->eventTemplateId = $c->event_template_id;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $data = $this->validatedPayload();

        $c = Category::find($this->editingId);
        if (! $c) return;

        if ($data['name'] !== $c->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $c->id);
        }
        $c->update($data);
        $this->resetForm();
        $this->flash = 'Category updated.';
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $c = Category::find($id);
        if (! $c) return;

        $usage = $c->eventTemplatePositions()->count() + $c->positions()->count();
        if ($usage > 0) {
            $this->flash = "Can't delete \"{$c->name}\" — {$usage} position(s) or template(s) still reference it. Reassign or remove them first.";
            return;
        }

        $c->delete();
        $this->flash = 'Category deleted.';
    }

    public function getItemsProperty(): Collection
    {
        return Category::orderBy('name')->withCount(['eventTemplatePositions', 'positions'])->get();
    }

    private function validatedPayload(): array
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'eventTemplateId' => 'nullable|integer|exists:event_templates,id',
        ]);

        return [
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'event_template_id' => $this->eventTemplateId ?: null,
            'requires_age_certification' => $this->requiresAgeCertification,
        ];
    }

    public function getEventTemplatesProperty(): Collection
    {
        return EventTemplate::orderBy('name')->get();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'editingId', 'requiresAgeCertification', 'eventTemplateId']);
        $this->color = '#4F46E5';
        $this->resetValidation();
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $n = 2;
        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId ?? 0)->exists()) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    public function render()
    {
        return view('livewire.admin.category-manager', [
            'items' => $this->items,
            'eventTemplates' => $this->eventTemplates,
        ]);
    }
}
