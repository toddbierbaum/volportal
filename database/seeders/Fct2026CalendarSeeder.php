<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Position;
use App\Models\PositionTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Imports the theater's actual 2026 calendar from Todd's volunteer
 * roster note. Idempotent via unique slug per event.
 */
class Fct2026CalendarSeeder extends Seeder
{
    public function run(): void
    {
        $standingShow = EventType::where('slug', 'standing-show')->firstOrFail();
        $kidsProduction = EventType::where('slug', 'kids-production')->firstOrFail();

        $houseManager = PositionTemplate::where('title', 'House Manager')->firstOrFail();
        $door = PositionTemplate::where('title', 'Door')->firstOrFail();
        $concessions = PositionTemplate::where('title', 'Concessions')->firstOrFail();
        $boxOffice = PositionTemplate::where('title', 'Box Office')->firstOrFail();

        $storytellerPositions = fn () => [
            [$houseManager, 1, -30],
            [$concessions, 2, -30],
            [$door, 1, -30],
        ];

        $kidsPositions = fn () => [
            [$houseManager, 1, -45],
            [$boxOffice, 1, -60],
            [$concessions, 2, -30],
            [$door, 1, -30],
        ];

        $events = [
            // --- Past (already happened this year) ---
            ['2026-01-23 18:00', '2026-01-23 20:30', 'Storytellers — January', $standingShow, $storytellerPositions],
            ['2026-02-27 18:00', '2026-02-27 20:30', 'Storytellers — February', $standingShow, $storytellerPositions],
            ['2026-03-27 18:00', '2026-03-27 20:30', 'Storytellers — March', $standingShow, $storytellerPositions],

            // --- Upcoming per the note ---
            ['2026-04-17 18:00', '2026-04-17 20:30', 'Storytellers — April', $standingShow, $storytellerPositions],

            ['2026-04-25 19:00', '2026-04-25 21:30', 'Annie Jr. — Friday Evening', $kidsProduction, $kidsPositions],
            ['2026-04-26 14:00', '2026-04-26 16:30', 'Annie Jr. — Saturday Matinee', $kidsProduction, $kidsPositions],

            ['2026-05-29 18:00', '2026-05-29 20:30', 'Storytellers — May', $standingShow, $storytellerPositions],
            ['2026-06-26 18:00', '2026-06-26 20:30', 'Storytellers — June', $standingShow, $storytellerPositions],
            ['2026-07-31 18:00', '2026-07-31 20:30', 'Storytellers — July', $standingShow, $storytellerPositions],
            ['2026-08-28 18:00', '2026-08-28 20:30', 'Storytellers — August', $standingShow, $storytellerPositions],
            ['2026-09-25 18:00', '2026-09-25 20:30', 'Storytellers — September', $standingShow, $storytellerPositions],
            ['2026-10-16 18:00', '2026-10-16 20:30', 'Storytellers — October', $standingShow, $storytellerPositions],
            ['2026-11-06 18:00', '2026-11-06 20:30', 'Storytellers — November', $standingShow, $storytellerPositions],
            ['2026-12-11 18:00', '2026-12-11 20:30', 'Storytellers — December', $standingShow, $storytellerPositions],
        ];

        foreach ($events as [$startStr, $endStr, $title, $type, $positionBuilder]) {
            $starts = Carbon::parse($startStr);
            $ends = Carbon::parse($endStr);
            $slug = Str::slug($title . ' ' . $starts->format('Y-m-d'));

            $event = Event::updateOrCreate(
                ['slug' => $slug],
                [
                    'event_type_id' => $type->id,
                    'title' => $title,
                    'description' => null,
                    'starts_at' => $starts,
                    'ends_at' => $ends,
                    'location' => 'Main Stage',
                    'is_published' => true,
                ]
            );

            if ($event->positions()->count() > 0) {
                continue;
            }

            foreach ($positionBuilder() as [$template, $slots, $offsetMinutes]) {
                Position::create([
                    'event_id' => $event->id,
                    'position_template_id' => $template->id,
                    'category_id' => $template->category_id,
                    'title' => $template->title,
                    'description' => $template->description,
                    'slots_needed' => $slots,
                    'is_public' => true,
                    'starts_at' => $starts->copy()->addMinutes($offsetMinutes),
                    'ends_at' => $ends,
                ]);
            }
        }
    }
}
