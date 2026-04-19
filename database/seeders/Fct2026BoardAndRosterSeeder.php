<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Signup;
use App\Models\User;
use App\Support\SmsSender;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One-shot import: seeds board members + non-board volunteers and
 * attaches them to the past 2026 events they actually worked
 * (per Todd's volunteer roster note).
 *
 * Idempotent:
 * - Users matched by email. firstOrCreate means existing rows are
 *   not clobbered (so running this after someone's changed their
 *   password won't reset them).
 * - Signups are unique on (user_id, position_id) at the schema level,
 *   so re-runs don't duplicate attendance records.
 */
class Fct2026BoardAndRosterSeeder extends Seeder
{
    private const DEFAULT_ADMIN_PASSWORD = 'change-me-on-first-login';

    public function run(): void
    {
        $users = $this->importUsers();
        $this->attachPastEventAttendance($users);
    }

    /**
     * @return array<string, User>  keyed by full name
     */
    private function importUsers(): array
    {
        // [name, email, phone, role]
        $rows = [
            // From Todd's board CSV. Skipping Todd (already seeded).
            ['Janet Windsor',         'jwindsor671@icloud.com',         '850-585-4977', 'volunteer'],
            ['Chelsea Martin',        'Chelsea.martin91@yahoo.com',     '850-635-2397', 'volunteer'],
            ['Corey Kotowske',        'coreykotowske@gmail.com',        '850-499-4105', 'admin'],
            ['Chelsea Campbell-Work', 'chelseacampbellwork@gmail.com',  '850-585-6413', 'volunteer'],
            ['Nathan Frymire',        'frydaddy1212@gmail.com',         '850-419-4005', 'volunteer'],
            ['Deann Bertram',         'dbertram@waltonso.org',          '850-419-2468', 'admin'],
            ['Caleb Lawrence',        'Caleb.lawrence@cox.net',         '850-603-0645', 'admin'],
            ['Julie White',           'julieauburnalum@gmail.com',      '850-419-1253', 'volunteer'],
            ['Brian Hawkins',         'brianhawkins@allstate.com',      '850-582-7348', 'volunteer'],
            ['Valerie Angel',         'vaa12180@gmail.com',             '281-949-8445', 'admin'],

            // Named in the roster but not on the CSV — added per Todd.
            ['Amanda Bierbaum',       'amanda@soireedfs.com',           '850-502-6886', 'volunteer'],
            // Mark + Artemis are Valerie's family — plus-addressed to her
            // inbox, sharing her phone. SMS opt-in off so Valerie doesn't
            // get 3x texts. They can get their own email/phone later.
            ['Mark Angel',            'vaa12180+mark@gmail.com',        '281-949-8445', 'volunteer'],
            ['Artemis Angel',         'vaa12180+artemis@gmail.com',     '281-949-8445', 'volunteer'],
        ];

        $users = [];

        // Existing Todd (from DatabaseSeeder) so the roster can reference him.
        $todd = User::where('email', 'todd.bierbaum@gmail.com')->first();
        if ($todd) {
            $users['Todd Bierbaum'] = $todd;
        }

        foreach ($rows as [$name, $email, $phone, $role]) {
            $attrs = [
                'name' => $name,
                'phone' => SmsSender::toE164($phone) ?? $phone,
                'role' => $role,
                'email_verified_at' => now(),
            ];
            if ($role === 'admin') {
                $attrs['password'] = Hash::make(self::DEFAULT_ADMIN_PASSWORD);
            }

            $user = User::firstOrCreate(['email' => $email], $attrs);
            $users[$name] = $user;

            $this->command->line(sprintf(
                '  %s %s <%s> [%s]',
                $user->wasRecentlyCreated ? '+ new' : '= kept',
                $name, $email, $role,
            ));
        }

        return $users;
    }

    /**
     * Create attended signups for past events per Todd's roster note.
     *
     * @param  array<string, User>  $users
     */
    private function attachPastEventAttendance(array $users): void
    {
        // Events keyed by slug; each maps position title -> list of
        // volunteer names. Slugs match Fct2026CalendarSeeder output.
        $roster = [
            'storytellers-january-2026-01-23' => [
                'House Manager' => ['Caleb Lawrence'],
                'Concessions'   => ['Valerie Angel', 'Mark Angel'],
                'Door'          => ['Amanda Bierbaum'],
            ],
            'storytellers-february-2026-02-27' => [
                'House Manager' => ['Todd Bierbaum'],
                'Concessions'   => ['Deann Bertram', 'Chelsea Campbell-Work'],
                'Door'          => ['Julie White'],
            ],
            'storytellers-march-2026-03-27' => [
                'House Manager' => ['Todd Bierbaum'],
                'Concessions'   => ['Mark Angel', 'Valerie Angel'],
                'Door'          => ['Artemis Angel'],
            ],
            'storytellers-april-2026-04-17' => [
                'Concessions'   => ['Valerie Angel'],
            ],
        ];

        foreach ($roster as $eventSlug => $positionAssignments) {
            $event = Event::where('slug', $eventSlug)->with('positions')->first();
            if (! $event) {
                $this->command->warn("  missing event: {$eventSlug} — skipping");
                continue;
            }

            foreach ($positionAssignments as $positionTitle => $volunteerNames) {
                $positionsForTitle = $event->positions->where('title', $positionTitle)->values();
                if ($positionsForTitle->isEmpty()) {
                    $this->command->warn("  {$event->title}: no '{$positionTitle}' position — skipping");
                    continue;
                }

                // Multiple slots of the same position title (e.g. 2x Concessions)
                // are represented as one Position row with slots_needed=N,
                // not multiple rows. All volunteers on that title attach to
                // the same Position.
                $position = $positionsForTitle->first();
                $durationMinutes = $position->starts_at->diffInMinutes($position->ends_at);
                $hours = round($durationMinutes / 60, 2);

                foreach ($volunteerNames as $name) {
                    $user = $users[$name] ?? null;
                    if (! $user) {
                        $this->command->warn("  {$event->title}: no user for '{$name}' — skipping");
                        continue;
                    }

                    $signup = Signup::updateOrCreate(
                        ['user_id' => $user->id, 'position_id' => $position->id],
                        [
                            'status' => 'attended',
                            'hours_worked' => $hours,
                            'checked_in_at' => $event->starts_at,
                        ]
                    );

                    $this->command->line(sprintf(
                        '  %s %s -> %s / %s (%s hrs)',
                        $signup->wasRecentlyCreated ? '+' : '=',
                        $name, $event->title, $positionTitle, $hours,
                    ));
                }
            }
        }
    }
}
