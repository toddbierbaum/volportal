<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class AdminTotpController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function showEnroll(Request $request)
    {
        $user = $request->user();

        // Reuse the same secret if returning after a failed confirmation attempt,
        // so the user doesn't need to rescan the QR code.
        $encryptedSecret = old('encrypted_secret');
        $secret = null;

        if ($encryptedSecret) {
            try {
                $secret = Crypt::decryptString($encryptedSecret);
            } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                // fall through to generate a fresh secret below
            }
        }

        if (! $secret) {
            $secret = $this->google2fa->generateSecretKey();
            $encryptedSecret = Crypt::encryptString($secret);
        }

        $qrUrl = $this->google2fa->getQRCodeUrl(
            'FCT Volunteer Portal',
            $user->email,
            $secret,
        );

        $svg = (new Writer(
            new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd())
        ))->writeString($qrUrl);

        return view('admin.totp.enroll', [
            'secret'          => $secret,
            'encryptedSecret' => $encryptedSecret,
            'qrSvg'           => $svg,
        ]);
    }

    public function confirm(Request $request)
    {
        $dbg = storage_path('totp_debug.txt');
        file_put_contents($dbg,
            'time: ' . now()->utc()->format('H:i:s') . "\n" .
            'has_encrypted_secret: ' . ($request->has('encrypted_secret') ? 'yes (' . strlen((string) $request->encrypted_secret) . ' chars)' : 'NO') . "\n" .
            'code: ' . ($request->has('code') ? $request->code : 'MISSING') . "\n"
        );

        $request->validate([
            'code'             => 'required|digits:6',
            'encrypted_secret' => 'required|string',
        ]);

        try {
            $secret = Crypt::decryptString($request->encrypted_secret);
            file_put_contents($dbg, "decrypt: OK (secret length: " . strlen($secret) . ")\n", FILE_APPEND);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            file_put_contents($dbg, "decrypt: FAILED — " . $e->getMessage() . "\n", FILE_APPEND);
            return back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code, 4);
        file_put_contents($dbg, "verify: " . ($valid ? 'OK' : 'FAILED') . "\n", FILE_APPEND);

        if (! $valid) {
            return back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $user = $request->user();
        $user->totp_secret = $secret;
        $user->totp_enabled_at = now();
        $user->save();
        file_put_contents($dbg, "save: OK (db totp_secret set: " . ($user->fresh()->totp_secret ? 'yes' : 'NO') . ")\n", FILE_APPEND);

        $request->session()->put('totp_verified', true);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Two-factor authentication is now active on your account.');
    }

    public function showChallenge(Request $request)
    {
        if ($request->session()->get('totp_verified')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.totp.challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = $request->user();

        if (! $this->google2fa->verifyKey($user->totp_secret, $request->code, 4)) {
            return back()->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $request->session()->put('totp_verified', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        $user->totp_secret = null;
        $user->totp_enabled_at = null;
        $user->save();

        $request->session()->forget('totp_verified');

        return redirect()->route('admin.totp.enroll')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}
