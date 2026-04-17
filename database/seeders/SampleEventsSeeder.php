<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Position;
use App\Models\PositionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleEventsSeeder extends Seeder
{
    public function run(): void
    {
        $standingShow = EventType::where('slug', 'standing-show')->firstOrFail();
        $kidsProduction = EventType::where('slug', 'kids-production')->firstOrFail();

        $houseManager = PositionTemplate::where('title', 'House Manager')->firstOrFail();
        $door = PositionTemplate::where('title', 'Door')->firstOrFail();
        $concessions = PositionTemplate::where('title', 'Concessions')->firstOrFail();
        $boxOffice = PositionTemplate::where('title', 'Box Office')->firstOrFail();

        $storytellers = $this->createEvent([
            'event_type_id' => $standingShow->id,
            'title' => 'Storytellers — May',
            'description' => 'Monthly Storytellers evening. Doors open 30 minutes before show.',
            'starts_at' => now()->addWeeks(2)->setTime(18, 0),
            'ends_at' => now()->addWeeks(2)->setTime(20, 30),
            'location' => 'Main Stage',
        ]);
        $this->addPosition($storytellers, $houseManager, 1, -30);
        $this->addPosition($storytellers, $concessions, 2, -30);
        $this->addPosition($storytellers, $door, 1, -30);

        $annieEvening = $this->createEvent([
            'event_type_id' => $kidsProduction->id,
            'title' => 'Annie Jr. — Friday Evening',
            'description' => 'Opening night performance of Annie Jr.',
            'starts_at' => now()->addWeeks(3)->setTime(19, 0),
            'ends_at' => now()->addWeeks(3)->setTime(21, 30),
            'location' => 'Main Stage',
        ]);
        $this->addPosition($annieEvening, $houseManager, 1, -45);
        $this->addPosition($annieEvening, $boxOffice, 1, -60);
        $this->addPosition($annieEvening, $concessions, 2, -30);
        $this->addPosition($annieEvening, $door, 1, -30);

        $annieMatinee = $this->createEvent([
            'event_type_id' => $kidsProduction->id,
            'title' => 'Annie Jr. — Saturday Matinee',
            'description' => 'Saturday afternoon performance of Annie Jr.',
            'starts_at' => now()->addWeeks(3)->addDay()->setTime(14, 0),
            'ends_at' => now()->addWeeks(3)->addDay()->setTime(16, 30),
            'location' => 'Main Stage',
        ]);
        $this->addPosition($annieMatinee, $houseManager, 1, -45);
        $this->addPosition($annieMatinee, $boxOffice, 1, -60);
        $this->addPosition($annieMatinee, $concessions, 2, -30);
        $this->addPosition($annieMatinee, $door, 1, -30);
    }

    private function createEvent(array $attributes): Event
    {
        $attributes['slug'] = Str::slug($attributes['title']) . '-' . Str::random(4);
        $attributes['is_published'] = true;
        return Event::create($attributes);
    }

    private function addPosition(Event $event, PositionTemplate $template, int $slots, int $startOffsetMinutes): void
    {
        Position::create([
            'event_id' => $event->id,
            'position_template_id' => $template->id,
            'category_id' => $template->category_id,
            'title' => $template->title,
            'description' => $template->description,
            'slots_needed' => $slots,
            'starts_at' => $event->starts_at->copy()->addMinutes($startOffsetMinutes),
            'ends_at' => $event->ends_at,
        ]);
    }
}
