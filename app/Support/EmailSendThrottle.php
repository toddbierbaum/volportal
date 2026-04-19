<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

/**
 * Three-layer rate limit for endpoints that send email from user input.
 *
 * Protects SendGrid credits and volunteer inboxes from:
 *  - repeated abuse from one IP,
 *  - repeated targeting of one email,
 *  - distributed credit-burn attacks (spraying many emails from many IPs).
 *
 * All three buckets decay on a rolling 60-min window, matching the
 * "60-min cooldown after hitting the limit" UX.
 */
class EmailSendThrottle
{
    private const PER_IP = 2;
    private const PER_EMAIL = 2;
    private const GLOBAL_CAP = 50;
    private const DECAY_SECONDS = 3600;

    public static function allow(string $email, string $ip): bool
    {
        $ipKey = "email-send-ip:$ip";
        $emailKey = 'email-send-email:'.strtolower($email);
        $globalKey = 'email-send-global';

        if (RateLimiter::tooManyAttempts($ipKey, self::PER_IP)
            || RateLimiter::tooManyAttempts($emailKey, self::PER_EMAIL)
            || RateLimiter::tooManyAttempts($globalKey, self::GLOBAL_CAP)) {
            return false;
        }

        RateLimiter::hit($ipKey, self::DECAY_SECONDS);
        RateLimiter::hit($emailKey, self::DECAY_SECONDS);
        RateLimiter::hit($globalKey, self::DECAY_SECONDS);

        return true;
    }
}
