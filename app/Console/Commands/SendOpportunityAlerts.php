<?php

namespace App\Console\Commands;

use App\Mail\OpportunityAlertsMail;
use App\Models\Setting;
use App\Models\Signup;
use App\Models\User;
use App\Support\OpportunityMatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('opportunities:send-alerts {--dry-run : List who would be emailed without sending}')]
#[Description('Email approved, opted-in volunteers a digest of open positions matching their interests')]
class SendOpportunityAlerts extends Command
{
    // How far out we look for open positions to include in the digest.
    // Matches the 60-day window used on the admin + volunteer dashboards.
    private const HORIZON_DAYS = 60;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if (! Setting::get('opportunity_alerts_enabled', true)) {
            $this->info('Monthly opportunity alerts are disabled in admin settings. Nothing sent.');
            return self::SUCCESS;
        }

        $horizon = now()->addDays(self::HORIZON_DAYS);

        $volunteers = User::query()
            ->where('role', 'volunteer')
            ->where('opportunity_alerts_opt_in', true)
            ->whereNotNull('approved_at')
            ->whereHas('categories')
            ->with('categories')
            ->get();

        if ($volunteers->isEmpty()) {
            $this->info('No opted-in volunteers to alert.');
            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($volunteers as $volunteer) {
            $alreadySignedUp = Signup::where('user_id', $volunteer->id)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->pluck('position_id')
                ->all();

            $positions = OpportunityMatcher::forUser($volunteer)
                ->reject(fn ($p) => in_array($p->id, $alreadySignedUp, true))
                ->filter(fn ($p) => $p->event->starts_at->lte($horizon))
                ->values();

            if ($positions->isEmpty()) {
                $skipped++;
                continue;
            }

            $this->line(sprintf(
                '%s %s — %d open position%s',
                $dryRun ? '[dry]' : '→',
                $volunteer->email,
                $positions->count(),
                $positions->count() === 1 ? '' : 's',
            ));

            if (! $dryRun) {
                Mail::to($volunteer->email)->send(new OpportunityAlertsMail($volunteer, $positions));
            }
            $sent++;
        }

        $this->info(sprintf(
            '%s %d volunteer%s, skipped %d with no matching open positions.',
            $dryRun ? 'Would email' : 'Emailed',
            $sent,
            $sent === 1 ? '' : 's',
            $skipped,
        ));

        return self::SUCCESS;
    }
}
