<?php

namespace App\Livewire\Admin;

use App\Models\NotificationSchedule;
use Illuminate\Support\Collection;
use Livewire\Component;

class NotificationScheduleManager extends Component
{
    public int $offsetValue = 1;
    public string $offsetUnit = 'days';
    public string $channel = 'email';

    public ?int $editingId = null;
    public string $flash = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function add(): void
    {
        $data = $this->validate([
            'offsetValue' => 'required|integer|min:1|max:52',
            'offsetUnit' => 'required|in:minutes,hours,days,weeks',
            'channel' => 'required|in:email,sms,both',
        ]);

        NotificationSchedule::create([
            'event_id' => null,
            'offset_minutes' => $this->toMinutes($data['offsetValue'], $data['offsetUnit']),
            'channel' => $data['channel'],
        ]);

        $this->resetForm();
        $this->flash = 'Reminder schedule added.';
    }

    public function startEdit(int $id): void
    {
        $s = NotificationSchedule::find($id);
        if (! $s || $s->event_id !== null) return;

        $this->editingId = $id;
        [$value, $unit] = $this->fromMinutes($s->offset_minutes);
        $this->offsetValue = $value;
        $this->offsetUnit = $unit;
        $this->channel = $s->channel ?? 'email';
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'offsetValue' => 'required|integer|min:1|max:52',
            'offsetUnit' => 'required|in:minutes,hours,days,weeks',
            'channel' => 'required|in:email,sms,both',
        ]);

        $s = NotificationSchedule::find($this->editingId);
        if (! $s) return;

        $s->update([
            'offset_minutes' => $this->toMinutes($data['offsetValue'], $data['offsetUnit']),
            'channel' => $data['channel'],
        ]);

        $this->resetForm();
        $this->flash = 'Reminder schedule updated.';
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $s = NotificationSchedule::find($id);
        if (! $s || $s->event_id !== null) return;

        $s->delete();
        $this->flash = 'Reminder schedule deleted.';
    }

    public function getItemsProperty(): Collection
    {
        return NotificationSchedule::whereNull('event_id')
            ->orderByDesc('offset_minutes')
            ->get();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId']);
        $this->offsetValue = 1;
        $this->offsetUnit = 'days';
        $this->channel = 'email';
        $this->resetValidation();
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

    /** @return array{0:int, 1:string} */
    private function fromMinutes(int $minutes): array
    {
        if ($minutes % 10080 === 0) return [$minutes / 10080, 'weeks'];
        if ($minutes % 1440 === 0)  return [$minutes / 1440, 'days'];
        if ($minutes % 60 === 0)    return [$minutes / 60, 'hours'];
        return [$minutes, 'minutes'];
    }

    public function render()
    {
        return view('livewire.admin.notification-schedule-manager', ['items' => $this->items]);
    }
}
