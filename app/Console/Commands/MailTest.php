<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('mail:test {to : Address to send the test email to}')]
#[Description('Send a one-off test email using the current MAIL_* config, useful for verifying SendGrid / SMTP setup post-deploy')]
class MailTest extends Command
{
    public function handle(): int
    {
        $to = (string) $this->argument('to');

        $this->line("Mail driver: " . config('mail.default'));
        $this->line("From: " . config('mail.from.address'));
        $this->line("Sending to: $to");

        try {
            Mail::raw(
                "This is a test email from " . config('app.name') . " (" . config('app.version') . ")."
                . "\nIf you received this, the MAIL_* configuration is working.",
                function ($m) use ($to) {
                    $m->to($to)->subject('Volunteer Portal — mail delivery test');
                }
            );
        } catch (\Throwable $e) {
            $this->error('Send failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Sent.');
        if (config('mail.default') === 'log') {
            $this->warn('MAIL_MAILER=log — email was written to storage/logs/laravel.log, not delivered.');
        }
        return self::SUCCESS;
    }
}
