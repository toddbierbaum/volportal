<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EventTemplate;
use App\Models\EventTemplatePosition;
use App\Models\EventTemplateSchedule;
use App\Models\NotificationSchedule;
use App\Models\User;
use App\Support\DurationFormatter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // firstOrCreate — never overwrites. updateOrCreate would reset
        // the password on every seed run, which silently rotated Todd's
        // credentials the first time someone re-ran seed in prod.
        User::firstOrCreate(
            ['email' => 'todd.bierbaum@gmail.com'],
            [
                'name' => 'Todd Bierbaum',
                'role' => 'admin',
                'password' => Hash::make('change-me-on-first-login'),
                'email_verified_at' => now(),
            ]
        );

        $categories = [
            ['name' => 'Front of House', 'slug' => 'front-of-house', 'color' => '#4F46E5',
             'description' => 'Welcoming and managing patrons — House Manager, Door, seating.',
             'requires_background_check' => false, 'requires_age_certification' => false],
            ['name' => 'Concessions',    'slug' => 'concessions',    'color' => '#F59E0B',
             'description' => 'Selling snacks and drinks during intermission.',
             'requires_background_check' => false, 'requires_age_certification' => true],
            ['name' => 'Box Office',     'slug' => 'box-office',     'color' => '#10B981',
             'description' => 'Ticket sales and will-call at the door.',
             'requires_background_check' => false, 'requires_age_certification' => false],
            ['name' => 'Backstage',      'slug' => 'backstage',      'color' => '#8B5CF6',
             'description' => 'Behind-the-scenes support — stage crew, set, props.',
             'requires_background_check' => false, 'requires_age_certification' => false],
            ['name' => 'Kids Productions', 'slug' => 'kids-productions', 'color' => '#EC4899',
             'description' => 'Volunteering at our youth performances and workshops.',
             'requires_background_check' => true, 'requires_age_certification' => false],
        ];
        foreach ($categories as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        $frontOfHouse = Category::where('slug', 'front-of-house')->value('id');
        $concessions  = Category::where('slug', 'concessions')->value('id');
        $boxOffice    = Category::where('slug', 'box-office')->value('id');

        // Default reminder offsets (in minutes). Seeded as both global
        // defaults (apply to every event) AND as each template's default
        // schedule list. The reminder command dedupes by offset so a
        // template that carries the same offset as a global only fires
        // one email per signup.
        $defaultSchedules = [10080, 1440]; // 1 week, 1 day

        foreach ($defaultSchedules as $offset) {
            NotificationSchedule::firstOrCreate(
                ['event_id' => null, 'offset_minutes' => $offset],
                ['label' => DurationFormatter::beforeEvent($offset)]
            );
        }

        // Call-time convention: HM 2 hr before showtime (120 min), everyone
        // else 1 hr (60 min). Shift ends 30 min after the 2-hour show ends
        // — so HM shift = 2h before + 2h show + 30min = 270 min, others =
        // 1h before + 2h show + 30min = 210 min.
        $templates = [
            [
                'slug' => 'standing-show',
                'name' => 'Standing Show',
                'color' => '#4F46E5',
                'positions' => [
                    ['title' => 'House Manager', 'category_id' => $frontOfHouse, 'slots' => 1, 'is_public' => false, 'call_offset' => 120, 'duration' => 270, 'order' => 10],
                    ['title' => 'Concessions',   'category_id' => $concessions,  'slots' => 2, 'is_public' => true,  'call_offset' =>  60, 'duration' => 210, 'order' => 20],
                    ['title' => 'Door',          'category_id' => $frontOfHouse, 'slots' => 1, 'is_public' => true,  'call_offset' =>  60, 'duration' => 210, 'order' => 30],
                ],
            ],
            [
                'slug' => 'kids-production',
                'name' => 'Kids Production',
                'color' => '#10B981',
                'positions' => [
                    ['title' => 'House Manager', 'category_id' => $frontOfHouse, 'slots' => 1, 'is_public' => false, 'call_offset' => 120, 'duration' => 270, 'order' => 10],
                    ['title' => 'Box Office',    'category_id' => $boxOffice,    'slots' => 1, 'is_public' => true,  'call_offset' =>  60, 'duration' => 210, 'order' => 20],
                    ['title' => 'Concessions',   'category_id' => $concessions,  'slots' => 2, 'is_public' => true,  'call_offset' =>  60, 'duration' => 210, 'order' => 30],
                    ['title' => 'Door',          'category_id' => $frontOfHouse, 'slots' => 1, 'is_public' => true,  'call_offset' =>  60, 'duration' => 210, 'order' => 40],
                ],
            ],
            [
                'slug' => 'rental',
                'name' => 'Rental',
                'color' => '#F59E0B',
                'positions' => [],
            ],
            [
                'slug' => 'other',
                'name' => 'Other',
                'color' => '#6B7280',
                'positions' => [],
            ],
        ];

        foreach ($templates as $tpl) {
            $template = EventTemplate::updateOrCreate(
                ['slug' => $tpl['slug']],
                ['name' => $tpl['name'], 'color' => $tpl['color']]
            );

            if ($template->positions()->count() === 0) {
                foreach ($tpl['positions'] as $p) {
                    EventTemplatePosition::create([
                        'event_template_id' => $template->id,
                        'category_id' => $p['category_id'],
                        'title' => $p['title'],
                        'slots_needed' => $p['slots'],
                        'is_public' => $p['is_public'],
                        'call_offset_minutes' => $p['call_offset'],
                        'duration_minutes' => $p['duration'],
                        'position_order' => $p['order'],
                    ]);
                }
            }

            if ($template->schedules()->count() === 0) {
                foreach ($defaultSchedules as $offset) {
                    EventTemplateSchedule::create([
                        'event_template_id' => $template->id,
                        'offset_minutes' => $offset,
                        'channel' => 'email',
                        'label' => DurationFormatter::beforeEvent($offset),
                    ]);
                }
            }
        }
    }
}
