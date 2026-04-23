<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('admin:disable-totp {email : The admin\'s email address}')]
#[Description('Clear TOTP 2FA for an admin account (recovery use only)')]
class DisableAdminTotp extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email: {$this->argument('email')}");
            return Command::FAILURE;
        }

        if (! $user->isAdmin()) {
            $this->error("User {$user->email} is not an admin.");
            return Command::FAILURE;
        }

        $user->update([
            'totp_secret'     => null,
            'totp_enabled_at' => null,
        ]);

        $this->info("TOTP disabled for {$user->email}. They will be prompted to re-enroll on next login.");
        return Command::SUCCESS;
    }
}
