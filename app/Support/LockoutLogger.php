<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Records a rate-limit lockout and returns a short reference ID the user can
 * include when emailing support. Operators grep `rate-limit.lockout` in
 * storage/logs/laravel.log and match on the ID to recover full context.
 */
class LockoutLogger
{
    public static function log(string $limiter, array $context = []): string
    {
        $id = Str::upper(Str::random(8));

        Log::warning('rate-limit.lockout', array_merge([
            'id' => $id,
            'limiter' => $limiter,
            'ip' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'path' => request()->path(),
            'method' => request()->method(),
            'user_id' => optional(request()->user())->getAuthIdentifier(),
        ], $context));

        return $id;
    }
}
