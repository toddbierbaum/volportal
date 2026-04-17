<?php

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\NotificationSchedule;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventScheduleManager extends Component
{
    public Event $event;

    public string $label = '';
    public int $offsetValue = 2;
    public string $offsetUnit = 'hours';

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function add(): void
    {
        $data = $this->validate([
            'label' => 'required|string|max:255',
            'offsetValue' => 'required|integer|min:1|max:52',
            'offsetUnit' => 'required|in:minutes,hours,days,weeks',
        ]);

        NotificationSchedule::create([
            'event_id' => $this->event->id,
            'label' => $data['label'],
            'offset_minutes' => $this->toMinutes($data['offsetValue'], $data['offsetUnit']),
        ]);

        $this->reset(['label']);
        $this->offsetValue = 2;
        $this->offsetUnit = 'hours';
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        NotificationSchedule::where('id', $id)
            ->where('event_id', $this->event->id)
            ->delete();
    }

    public function getGlobalSchedulesProperty(): Collection
    {
        return NotificationSchedule::whereNull('event_id')->orderByDesc('offset_minutes')->get();
    }

    public function getEventSchedulesProperty(): Collection
    {
        return NotificationSchedule::where('event_id', $this->event->id)->orderByDesc('offset_minutes')->get();
    }

    private function toMinutes(int $value, string $unit): int
    {
        return match ($unit) {
            'minutes' => $value,
            'hours' => $value * 60,
            'days' => $value * 1440,
            'weeks' => $value * 10080,
        };
    }

    public function render()
    {
        return view('livewire.admin.event-schedule-manager', [
            'globalSchedules' => $this->globalSchedules,
            'eventSchedules' => $this->eventSchedules,
        ]);
    }
}
