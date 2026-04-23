<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * Thin wrapper around the Twilio SDK so the rest of the app can send
 * SMS without knowing about Twilio specifics, and so tests can swap in
 * a fake. If TWILIO_* env vars aren't set, send() no-ops after logging
 * — useful for local/dev where you don't want real SMS dispatched.
 */
class SmsSender
{
    private ?Client $client = null;

    public function __construct(
        private readonly ?string $sid,
        private readonly ?string $token,
        private readonly ?string $from,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            sid: config('services.twilio.sid'),
            token: config('services.twilio.token'),
            from: config('services.twilio.from'),
        );
    }

    public function configured(): bool
    {
        return ! empty($this->sid) && ! empty($this->token) && ! empty($this->from);
    }

    /**
     * Convert a freeform US phone string to E.164 (+1XXXXXXXXXX).
     * Returns null if it can't be normalized to 10 digits.
     */
    public static function toE164(?string $phone): ?string
    {
        if (! $phone) return null;
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }
        if (strlen($digits) !== 10) return null;
        return '+1' . $digits;
    }

    /**
     * Send a single SMS. Returns true on success, false on any failure
     * (including unconfigured Twilio or unparseable phone number).
     */
    public function send(string $to, string $body): bool
    {
        $e164 = self::toE164($to);
        if (! $e164) {
            Log::warning('SMS skipped: un-normalizable phone number', ['to' => $to]);
            return false;
        }

        if (! $this->configured()) {
            Log::info('SMS skipped: Twilio not configured', ['to_suffix' => substr($e164, -4)]);
            return false;
        }

        try {
            $this->client()->messages->create($e164, [
                'from' => $this->from,
                'body' => $body,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('SMS send failed', ['to' => $e164, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function client(): Client
    {
        return $this->client ??= new Client($this->sid, $this->token);
    }
}
