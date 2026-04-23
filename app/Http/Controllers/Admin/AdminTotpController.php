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
            } catch (\Exception) {
                $secret = null;
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
            file_put_contents($dbg, file_get_contents($dbg) . "decrypt: OK (secret length: " . strlen($secret) . ")\n");
        } catch (\Exception $e) {
            file_put_contents($dbg, file_get_contents($dbg) . "decrypt: FAILED — " . $e->getMessage() . "\n");
            return back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code, 4);
        file_put_contents($dbg, file_get_contents($dbg) . "verify: " . ($valid ? 'OK' : 'FAILED') . "\n");

        if (! $valid) {
            return back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $request->user()->update([
            'totp_secret'     => $secret,
            'totp_enabled_at' => now(),
        ]);

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
        $request->user()->update([
            'totp_secret'     => null,
            'totp_enabled_at' => null,
        ]);

        $request->session()->forget('totp_verified');

        return redirect()->route('admin.totp.enroll')
            ->with('status', 'Two-factor authentication has been disabled.');
    }
}
