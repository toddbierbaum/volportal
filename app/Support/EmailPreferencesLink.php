<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailPreferencesLink
{
    // CAN-SPAM requires honoring opt-outs for at least 30 days after send;
    // 90 days gives recipients a comfortable window to unsubscribe from
    // older messages without forcing them to request a fresh login link.
    public static function for(User $user): string
    {
        return URL::temporarySignedRoute(
            'email-preferences',
            now()->addDays(90),
            ['user' => $user->id]
        );
    }
}
