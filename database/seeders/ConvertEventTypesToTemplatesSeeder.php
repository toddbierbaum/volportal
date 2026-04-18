<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\EventTemplatePosition;
use App\Models\EventTemplateSchedule;
use App\Models\EventType;
use App\Models\NotificationSchedule;
use Illuminate\Database\Seeder;

/**
 * Converts legacy EventTypes + global NotificationSchedules into the new
 * EventTemplate structure. Default positions per template are derived
 * from the theater's known operational patterns (see project memory:
 * Storytellers = HM + 2x Concessions + Door; Kids Production adds
 * Box Office). Other/rental types get no default positions — admins
 * add their own on each event.
 *
 * Idempotent: re-running this seeder updates existing templates in
 * place rather than creating duplicates.
 */
class ConvertEventTypesToTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPositionsBySlug = [
            'standing-show' => [
                ['title' => 'House Manager', 'category' => 'front-of-house', 'slots' => 1, 'is_public' => false, 'call_offset' => 30, 'duration' => 180, 'order' => 10],
                ['title' => 'Concessions', 'category' => 'concessions', 'slots' => 2, 'is_public' => true, 'call_offset' => 30, 'duration' => 180, 'order' => 20],
                ['title' => 'Door', 'category' => 'front-of-house', 'slots' => 1, 'is_public' => true, 'call_offset' => 30, 'duration' => 150, 'order' => 30],
            ],
            'kids-production' => [
                ['title' => 'House Manager', 'category' => 'front-of-house', 'slots' => 1, 'is_public' => false, 'call_offset' => 45, 'duration' => 210, 'order' => 10],
                ['title' => 'Box Office', 'category' => 'box-office', 'slots' => 1, 'is_public' => true, 'call_offset' => 60, 'duration' => 180, 'order' => 20],
                ['title' => 'Concessions', 'category' => 'concessions', 'slots' => 2, 'is_public' => true, 'call_offset' => 30, 'duration' => 180, 'order' => 30],
                ['title' => 'Door', 'category' => 'front-of-house', 'slots' => 1, 'is_public' => true, 'call_offset' => 30, 'duration' => 150, 'order' => 40],
            ],
            'rental' => [],
            'other' => [],
        ];

        $globalSchedules = NotificationSchedule::whereNull('event_id')->get();
        $categoriesBySlug = Category::pluck('id', 'slug');

        foreach (EventType::all() as $type) {
            $template = EventTemplate::updateOrCreate(
                ['slug' => $type->slug],
                [
                    'name' => $type->name,
                    'color' => $type->color,
                    'description' => null,
                ]
            );

            // Relink events from the type to the new template.
            Event::where('event_type_id', $type->id)->update(['event_template_id' => $template->id]);

            // Seed default positions for this template if it has none yet.
            if ($template->positions()->count() === 0 && isset($defaultPositionsBySlug[$type->slug])) {
                foreach ($defaultPositionsBySlug[$type->slug] as $p) {
                    $categoryId = $categoriesBySlug[$p['category']] ?? null;
                    if (! $categoryId) continue;

                    EventTemplatePosition::create([
                        'event_template_id' => $template->id,
                        'category_id' => $categoryId,
                        'title' => $p['title'],
                        'slots_needed' => $p['slots'],
                        'is_public' => $p['is_public'],
                        'call_offset_minutes' => $p['call_offset'],
                        'duration_minutes' => $p['duration'],
                        'position_order' => $p['order'],
                    ]);
                }
            }

            // Seed default schedules for this template if it has none yet.
            if ($template->schedules()->count() === 0) {
                foreach ($globalSchedules as $g) {
                    EventTemplateSchedule::create([
                        'event_template_id' => $template->id,
                        'offset_minutes' => $g->offset_minutes,
                        'channel' => 'email',
                    ]);
                }
            }
        }
    }
}
