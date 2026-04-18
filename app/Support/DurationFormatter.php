<?php

namespace App\Support;

class DurationFormatter
{
    /**
     * Render a minute count as a human-readable "X units before" phrase.
     * Examples: 10080 -> "1 week before", 1560 -> "1 day 2 hours before",
     * 90 -> "1 hour 30 minutes before", 45 -> "45 minutes before".
     */
    public static function beforeEvent(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'at event start';
        }
        return self::relative($minutes) . ' before';
    }

    /**
     * Render a minute count as a bare duration, no "before" suffix.
     */
    public static function relative(int $minutes): string
    {
        if ($minutes <= 0) {
            return 'at event start';
        }

        $weeks = intdiv($minutes, 10080);
        $remainder = $minutes % 10080;
        $days = intdiv($remainder, 1440);
        $remainder = $remainder % 1440;
        $hours = intdiv($remainder, 60);
        $mins = $remainder % 60;

        // Prefer at most two most-significant units, rounding down smaller ones
        // so "8 days 3 hours 27 minutes" renders as "1 week 1 day" which is
        // generally what an admin means when scheduling a reminder.
        $parts = [];
        if ($weeks > 0) {
            $parts[] = self::unit($weeks, 'week');
            if ($days > 0) $parts[] = self::unit($days, 'day');
        } elseif ($days > 0) {
            $parts[] = self::unit($days, 'day');
            if ($hours > 0) $parts[] = self::unit($hours, 'hour');
        } elseif ($hours > 0) {
            $parts[] = self::unit($hours, 'hour');
            if ($mins > 0) $parts[] = self::unit($mins, 'minute');
        } else {
            $parts[] = self::unit($mins, 'minute');
        }

        return implode(' ', $parts);
    }

    private static function unit(int $count, string $noun): string
    {
        return $count . ' ' . $noun . ($count === 1 ? '' : 's');
    }
}
