<?php

namespace App\Console\Commands;

use App\Mail\PendingVolunteersDigestMail;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

#[Signature('volunteers:send-pending-digest {--dry-run : List who would be emailed without sending}')]
#[Description('Email all admins a digest of volunteers still awaiting approval')]
class SendPendingVolunteersDigest extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $pending = User::query()
            ->where('role', 'volunteer')
            ->whereNull('approved_at')
            ->orderBy('created_at')
            ->get();

        $admins = User::query()
            ->where('role', 'admin')
            ->get();

        // Durable breadcrumb so we can see in the daily log whether the
        // scheduled run fired and what it found, even when stdout is discarded.
        Log::info('pending-volunteers digest: ran', [
            'pending' => $pending->count(),
            'admins' => $admins->count(),
            'dry_run' => $dryRun,
        ]);

        if ($pending->isEmpty()) {
            $this->info('No pending volunteers. Nothing sent.');
            return self::SUCCESS;
        }

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Nothing sent.');
            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%d pending volunteer%s → notifying %d admin%s',
            $pending->count(),
            $pending->count() === 1 ? '' : 's',
            $admins->count(),
            $admins->count() === 1 ? '' : 's',
        ));

        $sent = 0;
        foreach ($admins as $admin) {
            if ($dryRun) {
                $this->line(sprintf('[dry] %s', $admin->email));
                continue;
            }

            $this->line(sprintf('→ admin#%d', $admin->id));
            try {
                Mail::to($admin->email)->send(new PendingVolunteersDigestMail($admin, $pending));
                $sent++;
            } catch (\Throwable $e) {
                Log::error('pending-volunteers digest: send failed', [
                    'admin' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
                $this->error(sprintf('Failed to email %s: %s', $admin->email, $e->getMessage()));
            }
        }

        if (! $dryRun) {
            Log::info('pending-volunteers digest: done', ['sent' => $sent, 'admins' => $admins->count()]);
        }

        return self::SUCCESS;
    }
}
