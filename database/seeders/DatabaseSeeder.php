<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EventType;
use App\Models\PositionTemplate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'todd.bierbaum@gmail.com'],
            [
                'name' => 'Todd Bierbaum',
                'role' => 'admin',
                'password' => Hash::make('change-me-on-first-login'),
                'email_verified_at' => now(),
            ]
        );

        $eventTypes = [
            ['name' => 'Standing Show',    'slug' => 'standing-show',    'color' => '#4F46E5'],
            ['name' => 'Kids Production',  'slug' => 'kids-production',  'color' => '#10B981'],
            ['name' => 'Rental',           'slug' => 'rental',           'color' => '#F59E0B'],
            ['name' => 'Other',            'slug' => 'other',            'color' => '#6B7280'],
        ];
        foreach ($eventTypes as $t) {
            EventType::updateOrCreate(['slug' => $t['slug']], $t);
        }

        $categories = [
            ['name' => 'Front of House', 'slug' => 'front-of-house', 'color' => '#4F46E5',
             'description' => 'Welcoming and managing patrons — House Manager, Door, seating.'],
            ['name' => 'Concessions',    'slug' => 'concessions',    'color' => '#F59E0B',
             'description' => 'Selling snacks and drinks during intermission.'],
            ['name' => 'Box Office',     'slug' => 'box-office',     'color' => '#10B981',
             'description' => 'Ticket sales and will-call at the door.'],
            ['name' => 'Backstage',      'slug' => 'backstage',      'color' => '#8B5CF6',
             'description' => 'Behind-the-scenes support — stage crew, set, props.'],
        ];
        foreach ($categories as $c) {
            Category::updateOrCreate(['slug' => $c['slug']], $c);
        }

        $frontOfHouse = Category::where('slug', 'front-of-house')->first();
        $concessions  = Category::where('slug', 'concessions')->first();
        $boxOffice    = Category::where('slug', 'box-office')->first();

        $templates = [
            ['title' => 'House Manager', 'category_id' => $frontOfHouse->id, 'default_duration_minutes' => 180,
             'description' => 'Manages front-of-house operations during the event.'],
            ['title' => 'Door',          'category_id' => $frontOfHouse->id, 'default_duration_minutes' => 150,
             'description' => 'Greets patrons, checks tickets, directs seating.'],
            ['title' => 'Concessions',   'category_id' => $concessions->id,  'default_duration_minutes' => 180,
             'description' => 'Staffs the concessions counter before the show and at intermission.'],
            ['title' => 'Box Office',    'category_id' => $boxOffice->id,    'default_duration_minutes' => 150,
             'description' => 'Sells tickets and handles will-call at the door.'],
        ];
        foreach ($templates as $tpl) {
            PositionTemplate::updateOrCreate(
                ['title' => $tpl['title']],
                $tpl
            );
        }
    }
}
