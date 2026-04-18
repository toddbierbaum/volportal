<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data migration: copy legacy event_types + global notification_schedules
 * into the new event_templates structure, then relink existing events
 * from event_type_id to event_template_id. Runs after the additive
 * M14.A migrations (which created the new tables) and before the M14.C
 * drops. Uses raw DB queries instead of Eloquent models so this works
 * even on fresh installs where the legacy models no longer exist.
 *
 * Safe to run multiple times: templates are matched by slug, and
 * positions/schedules are only seeded onto empty templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_types') || ! Schema::hasTable('event_templates')) {
            return;
        }

        $defaultPositionsBySlug = [
            'standing-show' => [
                ['House Manager', 'front-of-house', 1, false, 30,  180, 10],
                ['Concessions',   'concessions',    2, true,  30,  180, 20],
                ['Door',          'front-of-house', 1, true,  30,  150, 30],
            ],
            'kids-production' => [
                ['House Manager', 'front-of-house', 1, false, 45,  210, 10],
                ['Box Office',    'box-office',     1, true,  60,  180, 20],
                ['Concessions',   'concessions',    2, true,  30,  180, 30],
                ['Door',          'front-of-house', 1, true,  30,  150, 40],
            ],
            'rental' => [],
            'other' => [],
        ];

        $categoryIds = DB::table('categories')->pluck('id', 'slug');
        $now = now();
        $globalScheduleOffsets = DB::table('notification_schedules')
            ->whereNull('event_id')
            ->pluck('offset_minutes');

        foreach (DB::table('event_types')->get() as $type) {
            $templateId = DB::table('event_templates')->where('slug', $type->slug)->value('id');

            if (! $templateId) {
                $templateId = DB::table('event_templates')->insertGetId([
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'color' => $type->color,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('events')
                ->where('event_type_id', $type->id)
                ->update(['event_template_id' => $templateId]);

            $existingPositions = DB::table('event_template_positions')
                ->where('event_template_id', $templateId)
                ->count();

            if ($existingPositions === 0 && isset($defaultPositionsBySlug[$type->slug])) {
                foreach ($defaultPositionsBySlug[$type->slug] as [$title, $categorySlug, $slots, $isPublic, $callOffset, $duration, $order]) {
                    $categoryId = $categoryIds[$categorySlug] ?? null;
                    if (! $categoryId) continue;

                    DB::table('event_template_positions')->insert([
                        'event_template_id' => $templateId,
                        'category_id' => $categoryId,
                        'title' => $title,
                        'slots_needed' => $slots,
                        'is_public' => $isPublic,
                        'call_offset_minutes' => $callOffset,
                        'duration_minutes' => $duration,
                        'position_order' => $order,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $existingSchedules = DB::table('event_template_schedules')
                ->where('event_template_id', $templateId)
                ->count();

            if ($existingSchedules === 0) {
                foreach ($globalScheduleOffsets as $offsetMinutes) {
                    DB::table('event_template_schedules')->insert([
                        'event_template_id' => $templateId,
                        'offset_minutes' => $offsetMinutes,
                        'channel' => 'email',
                        'label' => $this->humanLabel((int) $offsetMinutes),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // Global notification_schedules are kept as site-wide defaults.
        // The reminder command dedupes by offset_minutes per signup so
        // a global + event-level schedule at the same offset only fires
        // one email.
    }

    public function down(): void
    {
        // Data migration — nothing to undo.
    }

    private function humanLabel(int $minutes): string
    {
        if ($minutes <= 0) return 'at event start';
        if ($minutes % 10080 === 0) {
            $w = intdiv($minutes, 10080);
            return $w . ' week' . ($w === 1 ? '' : 's') . ' before';
        }
        if ($minutes % 1440 === 0) {
            $d = intdiv($minutes, 1440);
            return $d . ' day' . ($d === 1 ? '' : 's') . ' before';
        }
        if ($minutes % 60 === 0) {
            $h = intdiv($minutes, 60);
            return $h . ' hour' . ($h === 1 ? '' : 's') . ' before';
        }
        return $minutes . ' minute' . ($minutes === 1 ? '' : 's') . ' before';
    }
};
