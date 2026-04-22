<?php

namespace App\Console\Commands;

use App\Mail\PendingVolunteersDigestMail;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
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

        if ($pending->isEmpty()) {
            $this->info('No pending volunteers. Nothing sent.');
            return self::SUCCESS;
        }

        $admins = User::query()
            ->where('role', 'admin')
            ->get();

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

        foreach ($admins as $admin) {
            $this->line(sprintf('%s %s', $dryRun ? '[dry]' : '→', $admin->email));

            if (! $dryRun) {
                Mail::to($admin->email)->send(new PendingVolunteersDigestMail($admin, $pending));
            }
        }

        return self::SUCCESS;
    }
}
