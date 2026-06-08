<?php

namespace App\Console\Commands;

use App\Support\SmsSender;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sms:test {to : US phone number to send the test SMS to}')]
#[Description('Send a one-off test SMS via Twilio using the current TWILIO_* config, useful for verifying credentials post-deploy')]
class SmsTest extends Command
{
    public function handle(): int
    {
        $to = (string) $this->argument('to');
        $sms = SmsSender::fromConfig();

        $this->line('From: ' . (config('services.twilio.from') ?: '(unset)'));
        $this->line("Sending to: $to");

        if (! $sms->configured()) {
            $this->warn('TWILIO_* env vars are not all set — send() will log and no-op.');
        }

        $body = config('app.name') . ' SMS delivery test. If you got this, Twilio is wired up.';

        $ok = $sms->send($to, $body);
        if (! $ok) {
            $this->error('Send failed. Check storage/logs/laravel.log for the reason.');
            return self::FAILURE;
        }

        $this->info('Sent.');
        return self::SUCCESS;
    }
}
