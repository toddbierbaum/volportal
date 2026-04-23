<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

class EmailPreferencesLink
{
    // CAN-SPAM requires honoring opt-outs for 30 days; 7 days balances that
    // requirement against the security cost of long-lived bearer-like links.
    public static function for(User $user): string
    {
        return URL::temporarySignedRoute(
            'email-preferences',
            now()->addDays(7),
            ['user' => $user->id]
        );
    }
}
