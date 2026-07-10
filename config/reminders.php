<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum reminder staleness (minutes)
    |--------------------------------------------------------------------------
    |
    | A reminder is skipped if its scheduled send time (an event's starts_at
    | minus the schedule's offset_minutes) is more than this many minutes in
    | the past. This stops a long-lead reminder (e.g. "1 week before") from
    | firing very late — say on event day — when it was never sent, while
    | still allowing short cron-outage catch-up. Offsets at or below this
    | value keep their entire window and are unaffected.
    |
    */

    'max_staleness_minutes' => (int) env('REMINDER_MAX_STALENESS_MINUTES', 1440),

];
