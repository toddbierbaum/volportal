<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Event;
use App\Models\Position;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class PositionEditor extends Component
{
    public Event $event;

    public ?int $categoryId = null;
    public string $title = '';
    public string $description = '';
    public int $slotsNeeded = 1;
    public bool $isPublic = true;
    public int $callOffsetMinutes = 60;
    public int $durationMinutes = 180;

    public ?int $editingPositionId = null;
    public bool $showAddForm = false;

    public ?int $assigningForPositionId = null;
    public ?int $selectedVolunteerId = null;

    public function mount(Event $event): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->event = $event;
    }

    public function startAdd(): void
    {
        $this->resetForm();
        $this->showAddForm = true;
    }

    public function addPosition(): void
    {
        $data = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'callOffsetMinutes' => 'required|integer|min:0|max:1440',
            'durationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        [$start, $end] = $this->computeTimes($data['callOffsetMinutes'], $data['durationMinutes']);

        $this->event->positions()->create([
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'starts_at' => $start,
            'ends_at' => $end,
        ]);

        $this->resetForm();
    }

    public function startEdit(int $positionId): void
    {
        $position = $this->event->positions()->find($positionId);
        if (! $position) return;

        $this->editingPositionId = $position->id;
        $this->categoryId = $position->category_id;
        $this->title = $position->title;
        $this->description = (string) $position->description;
        $this->slotsNeeded = $position->slots_needed;
        $this->isPublic = (bool) $position->is_public;

        [$callOffset, $duration] = $this->inferOffsets($position);
        $this->callOffsetMinutes = $callOffset;
        $this->durationMinutes = $duration;
    }

    public function saveEdit(): void
    {
        $position = $this->event->positions()->find($this->editingPositionId);
        if (! $position) return;

        $data = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categoryId' => 'required|exists:categories,id',
            'slotsNeeded' => 'required|integer|min:1|max:50',
            'callOffsetMinutes' => 'required|integer|min:0|max:1440',
            'durationMinutes' => 'required|integer|min:15|max:1440',
        ]);

        [$start, $end] = $this->computeTimes($data['callOffsetMinutes'], $data['durationMinutes']);

        $position->update([
            'category_id' => $data['categoryId'],
            'title' => $data['title'],
            'description' => $data['description'] ?: null,
            'slots_needed' => $data['slotsNeeded'],
            'is_public' => $this->isPublic,
            'starts_at' => $start,
            'ends_at' => $end,
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

    public function startAssigning(int $positionId): void
    {
        $this->assigningForPositionId = $positionId;
        $this->selectedVolunteerId = null;
    }

    public function cancelAssigning(): void
    {
        $this->assigningForPositionId = null;
        $this->selectedVolunteerId = null;
    }

    public function assign(): void
    {
        if (! $this->assigningForPositionId || ! $this->selectedVolunteerId) return;

        $position = $this->event->positions()->find($this->assigningForPositionId);
        if (! $position) return;

        $existing = Signup::where('user_id', $this->selectedVolunteerId)
            ->where('position_id', $position->id)
            ->first();

        // Backfill flow: if the event already happened, default the new
        // signup straight to 'attended' and auto-fill hours from the
        // position's scheduled duration. Admin can adjust hours after.
        $isPast = $this->event->starts_at->isPast();
        $defaultStatus = $isPast ? 'attended' : ($position->isFull() ? 'waitlisted' : 'confirmed');

        if ($existing) {
            if ($existing->status === 'cancelled' || ($isPast && $existing->status !== 'attended')) {
                $updates = ['status' => $defaultStatus];
                if ($defaultStatus === 'attended' && ! $existing->hours_worked) {
                    $duration = $position->starts_at->diffInMinutes($position->ends_at);
                    $updates['hours_worked'] = round($duration / 60, 2);
                    $updates['checked_in_at'] = now();
                }
                $existing->update($updates);
            }
        } else {
            $data = [
                'user_id' => $this->selectedVolunteerId,
                'position_id' => $position->id,
                'status' => $defaultStatus,
            ];
            if ($defaultStatus === 'attended') {
                $duration = $position->starts_at->diffInMinutes($position->ends_at);
                $data['hours_worked'] = round($duration / 60, 2);
                $data['checked_in_at'] = now();
            }
            Signup::create($data);
        }

        $this->cancelAssigning();
    }

    public function setStatus(int $signupId, string $status): void
    {
        if (! in_array($status, ['confirmed', 'waitlisted', 'cancelled', 'attended', 'no_show'])) return;

        $signup = Signup::whereHas('position', fn ($q) => $q->where('event_id', $this->event->id))
            ->find($signupId);
        if (! $signup) return;

        $updates = ['status' => $status];

        if ($status === 'attended' && ! $signup->hours_worked) {
            $duration = $signup->position->starts_at->diffInMinutes($signup->position->ends_at);
            $updates['hours_worked'] = round($duration / 60, 2);
            $updates['checked_in_at'] = now();
        }

        if ($status === 'no_show') {
            $updates['hours_worked'] = null;
        }

        $signup->update($updates);
    }

    public function setHours(int $signupId, string $hours): void
    {
        $signup = Signup::whereHas('position', fn ($q) => $q->where('event_id', $this->event->id))
            ->find($signupId);
        if (! $signup) return;

        $hoursValue = trim($hours) === '' ? null : (float) $hours;
        if ($hoursValue !== null && ($hoursValue < 0 || $hoursValue > 24)) return;

        $signup->update(['hours_worked' => $hoursValue]);
    }

    public function removeSignup(int $signupId): void
    {
        Signup::whereHas('position', fn ($q) => $q->where('event_id', $this->event->id))
            ->where('id', $signupId)
            ->delete();
    }

    /** @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon} */
    private function computeTimes(int $callOffsetMinutes, int $durationMinutes): array
    {
        $start = $this->event->starts_at->copy()->subMinutes($callOffsetMinutes);
        $end = $start->copy()->addMinutes($durationMinutes);
        return [$start, $end];
    }

    /** @return array{0: int, 1: int} */
    private function inferOffsets(Position $position): array
    {
        $callOffset = max(0, $this->event->starts_at->diffInMinutes($position->starts_at, false) * -1);
        $duration = max(15, (int) $position->starts_at->diffInMinutes($position->ends_at, false));
        return [(int) $callOffset, (int) $duration];
    }

    private function resetForm(): void
    {
        $this->reset(['categoryId', 'title', 'description', 'editingPositionId', 'showAddForm']);
        $this->slotsNeeded = 1;
        $this->isPublic = true;
        $this->callOffsetMinutes = 60;
        $this->durationMinutes = 180;
        $this->resetValidation();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function getPositionsProperty(): Collection
    {
        return $this->event->positions()
            ->with(['category', 'signups.user'])
            ->orderBy('starts_at')
            ->get();
    }

    public function getAvailableVolunteersProperty(): Collection
    {
        if (! $this->assigningForPositionId) return collect();

        $alreadyAssigned = Signup::where('position_id', $this->assigningForPositionId)
            ->whereIn('status', ['confirmed', 'waitlisted', 'attended'])
            ->pluck('user_id');

        return User::whereIn('role', ['volunteer', 'admin'])
            ->whereNotIn('id', $alreadyAssigned)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.position-editor', [
            'categories' => $this->categories,
            'positions' => $this->positions,
            'availableVolunteers' => $this->availableVolunteers,
            'isPast' => $this->event->starts_at->isPast(),
        ]);
    }
}
