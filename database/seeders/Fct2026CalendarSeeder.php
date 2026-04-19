<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Imports the theater's actual 2026 calendar from Todd's volunteer
 * roster note. Idempotent via unique slug per event.
 *
 * Rules (all events):
 *   - Show runs 2 hours (event.starts_at -> ends_at)
 *   - House Manager calls 2 hours before showtime
 *   - All other positions call 1 hour before showtime
 *   - Every shift ends 30 minutes after the show ends
 */
class Fct2026CalendarSeeder extends Seeder
{
    public function run(): void
    {
        $standingShow = EventTemplate::where('slug', 'standing-show')->firstOrFail();
        $kidsProduction = EventTemplate::where('slug', 'kids-production')->firstOrFail();

        $cats = Category::pluck('id', 'slug');

        // Standard 2-hour show + 30-min-after teardown = 2h30m of shift
        // anchored to showtime.
        //   HM:     call 120 min before show, shift runs 120 + 120 + 30 = 270 min
        //   Others: call  60 min before show, shift runs  60 + 120 + 30 = 210 min
        $storytellerPositions = fn () => [
            // title, category, slots, is_public, call_offset_min, duration_min
            ['House Manager', 'front-of-house', 1, false, 120, 270],
            ['Concessions',   'concessions',    2, true,   60, 210],
            ['Door',          'front-of-house', 1, true,   60, 210],
        ];

        $kidsPositions = fn () => [
            ['House Manager', 'front-of-house', 1, false, 120, 270],
            ['Box Office',    'box-office',     1, true,   60, 210],
            ['Concessions',   'concessions',    2, true,   60, 210],
            ['Door',          'front-of-house', 1, true,   60, 210],
        ];

        // All events are 2 hours. Showtime in the local (Central) timezone.
        $events = [
            // --- Past (already happened this year) ---
            ['2026-01-23 19:00', 'Storytellers — January',    $standingShow,   $storytellerPositions],
            ['2026-02-27 19:00', 'Storytellers — February',   $standingShow,   $storytellerPositions],
            ['2026-03-27 19:00', 'Storytellers — March',      $standingShow,   $storytellerPositions],

            // --- Upcoming ---
            ['2026-04-17 19:00', 'Storytellers — April',      $standingShow,   $storytellerPositions],

            ['2026-04-25 19:00', 'Annie Jr. — Friday Evening', $kidsProduction, $kidsPositions],
            ['2026-04-26 14:00', 'Annie Jr. — Sunday Matinee', $kidsProduction, $kidsPositions],

            ['2026-05-29 19:00', 'Storytellers — May',        $standingShow,   $storytellerPositions],
            ['2026-06-26 19:00', 'Storytellers — June',       $standingShow,   $storytellerPositions],
            ['2026-07-31 19:00', 'Storytellers — July',       $standingShow,   $storytellerPositions],
            ['2026-08-28 19:00', 'Storytellers — August',     $standingShow,   $storytellerPositions],
            ['2026-09-25 19:00', 'Storytellers — September',  $standingShow,   $storytellerPositions],
            ['2026-10-16 19:00', 'Storytellers — October',    $standingShow,   $storytellerPositions],
            ['2026-11-06 19:00', 'Storytellers — November',   $standingShow,   $storytellerPositions],
            ['2026-12-11 19:00', 'Storytellers — December',   $standingShow,   $storytellerPositions],
        ];

        foreach ($events as [$startStr, $title, $template, $positionBuilder]) {
            $starts = Carbon::parse($startStr);
            $ends = $starts->copy()->addHours(2);
            $slug = Str::slug($title . ' ' . $starts->format('Y-m-d'));

            $event = Event::updateOrCreate(
                ['slug' => $slug],
                [
                    'event_template_id' => $template->id,
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

            foreach ($positionBuilder() as [$posTitle, $categorySlug, $slots, $isPublic, $callOffset, $duration]) {
                $positionStart = $starts->copy()->subMinutes($callOffset);
                Position::create([
                    'event_id' => $event->id,
                    'category_id' => $cats[$categorySlug],
                    'title' => $posTitle,
                    'slots_needed' => $slots,
                    'is_public' => $isPublic,
                    'starts_at' => $positionStart,
                    'ends_at' => $positionStart->copy()->addMinutes($duration),
                ]);
            }
        }
    }
}
