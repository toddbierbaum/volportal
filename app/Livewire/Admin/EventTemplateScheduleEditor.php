<?php

namespace App\Livewire\Admin;

use App\Models\EventTemplate;
use App\Models\EventTemplateSchedule;
use Illuminate\Support\Collection;
use Livewire\Component;

class EventTemplateScheduleEditor extends Component
{
    public EventTemplate $template;

    public int $offsetValue = 1;
    public string $offsetUnit = 'days';
    public string $channel = 'email';

    public function mount(EventTemplate $template): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        $this->template = $template;
    }

    public function add(): void
    {
        $data = $this->validate([
            'offsetValue' => 'required|integer|min:1|max:52',
            'offsetUnit' => 'required|in:minutes,hours,days,weeks',
            'channel' => 'required|in:email,sms,both',
        ]);

        $this->template->schedules()->create([
            'offset_minutes' => $this->toMinutes($data['offsetValue'], $data['offsetUnit']),
            'channel' => $data['channel'],
        ]);

        $this->offsetValue = 1;
        $this->offsetUnit = 'days';
        $this->channel = 'email';
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        EventTemplateSchedule::where('id', $id)
            ->where('event_template_id', $this->template->id)
            ->delete();
    }

    public function getSchedulesProperty(): Collection
    {
        return $this->template->schedules()->get();
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
        return view('livewire.admin.event-template-schedule-editor', [
            'schedules' => $this->schedules,
        ]);
    }
}
