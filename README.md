# VolPortal

Volunteer portal for the [Florida Chautauqua Theater & Institute](https://floridachautauqua.com) in DeFuniak Springs, FL.

Live at **https://volunteer.floridachautauqua.com**.

Board members publish events, volunteers sign up from a public calendar, reminders go out by email (SMS coming), and hours get tracked for reporting.

---

## Stack

- **Laravel 13** (PHP 8.4) + **Livewire 3** + **Volt** + **Breeze** (auth scaffolding)
- **SQLite** database — single-file, lives at `database/database.sqlite`
- **Tailwind CSS** (v3)
- **SendGrid** for transactional email (SMTP)
- **Twilio** for SMS (pending toll-free verification)
- Hosted on **DreamHost shared hosting**, deployed by SSH + git pull
- Timezone: `America/Chicago` (theater is in the Central Time half of the FL panhandle)

## Local development

Requires PHP 8.4, Composer, Node 22, and SQLite.

```bash
git clone git@github.com:toddbierbaum/volportal.git
cd volportal
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run dev        # in one terminal
php artisan serve  # in another
```

Then open http://localhost:8000.

Fresh seed creates:

- One admin (`todd.bierbaum@gmail.com`, password in [`DatabaseSeeder.php`](database/seeders/DatabaseSeeder.php))
- The 2026 calendar of events ([`Fct2026CalendarSeeder`](database/seeders/Fct2026CalendarSeeder.php))
- Optionally, board members + back-filled attendance: `php artisan db:seed --class=Fct2026BoardAndRosterSeeder`

## Production deploy

Deploys happen by SSH'ing into DreamHost and running [`deploy.sh`](deploy.sh):

```bash
ssh <user>@<host>
cd ~/volunteer.floridachautauqua.com
./deploy.sh
```

The script backs up the SQLite DB, pulls from GitHub, runs migrations, builds assets, and bumps the patch version (the `VERSION` file holds `MAJOR.MINOR`; the patch comes from `git rev-list --count HEAD`).

The reminder job is wired to cron via `schedule:run`.

## Domain model

- **EventTemplate** — reusable event type with default positions + notification schedules (e.g. "Standing Show", "Kids Production")
- **Event** — a specific show on a specific date; has many Positions
- **Position** — a role at an event (House Manager, Concessions, Door, Box Office…), with call time and shift duration
- **Signup** — a user claiming a position slot; statuses: `confirmed`, `waitlisted`, `attended`, `canceled`, `no_show`
- **Category** — tags on Positions used by the volunteer-interest matching flow
- **NotificationSchedule** — per-template or per-event reminder offsets ("2 days before", "1 hour before")
- **User** — `volunteer` or `admin` role; volunteers auth via magic link, admins via password

## Auth model

- **Volunteers** log in by requesting a magic link by email. No password.
- **Admins** log in with email + password. Magic-link login is blocked for admin accounts.
- `auth` middleware protects `/my/*`; `auth + admin` gates `/admin/*`.

## License

Proprietary — all rights reserved to the Florida Chautauqua Theater & Institute.
