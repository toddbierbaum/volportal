<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventSignupManager extends Component
{
    public Event $event;

    public ?int $assigningForPositionId = null;
    public ?int $selectedVolunteerId = null;

    public function mount(Event $event): void
    {
        $this->event = $event;
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

        if ($existing) {
            if ($existing->status === 'cancelled') {
                $existing->update(['status' => $position->isFull() ? 'waitlisted' : 'confirmed']);
            }
        } else {
            Signup::create([
                'user_id' => $this->selectedVolunteerId,
                'position_id' => $position->id,
                'status' => $position->isFull() ? 'waitlisted' : 'confirmed',
            ]);
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

        return User::where('role', 'volunteer')
            ->whereNotIn('id', $alreadyAssigned)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.event-signup-manager', [
            'positions' => $this->positions,
            'availableVolunteers' => $this->availableVolunteers,
            'isPast' => $this->event->starts_at->isPast(),
        ]);
    }
}
