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
    private const ENROLLMENT_TTL_SECONDS = 600;

    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function showEnroll(Request $request)
    {
        $user = $request->user();

        abort_if(
            $user->hasTotpEnabled() && ! $request->session()->get('totp_verified'),
            403
        );

        // Reuse the same secret if returning after a failed confirmation attempt,
        // so the user doesn't need to rescan the QR code.
        $secret = $this->decodeSecret(old('encrypted_secret'));
        $encryptedSecret = old('encrypted_secret');

        if (! $secret) {
            $secret = $this->google2fa->generateSecretKey();
            $encryptedSecret = $this->encodeSecret($secret);
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
        abort_if(
            $request->user()->hasTotpEnabled() && ! $request->session()->get('totp_verified'),
            403
        );

        $request->validate([
            'code'             => 'required|digits:6',
            'encrypted_secret' => 'required|string',
        ]);

        $secret = $this->decodeSecret($request->encrypted_secret);

        if (! $secret) {
            return redirect()->route('admin.totp.enroll')
                ->withErrors(['code' => 'Your enrollment session expired. Scan the new QR code and try again.']);
        }

        if (! $this->google2fa->verifyKey($secret, $request->code, 4)) {
            return back()
                ->withInput($request->except('code'))
                ->withErrors(['code' => 'That code is incorrect. Try again.']);
        }

        $user = $request->user();
        $user->totp_secret = $secret;
        $user->totp_enabled_at = now();
        $user->save();

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

    private function encodeSecret(string $secret): string
    {
        return Crypt::encryptString(json_encode([
            's' => $secret,
            't' => now()->timestamp,
        ]));
    }

    private function decodeSecret(?string $encrypted): ?string
    {
        if (! $encrypted) {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($encrypted), true);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return null;
        }

        if (! is_array($payload) || ! isset($payload['s'], $payload['t'])) {
            return null;
        }

        if (now()->timestamp - (int) $payload['t'] > self::ENROLLMENT_TTL_SECONDS) {
            return null;
        }

        return (string) $payload['s'];
    }
}
