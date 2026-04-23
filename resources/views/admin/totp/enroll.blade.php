<x-layouts.admin-auth title="Set Up Two-Factor Authentication">
    <h1 class="text-xl font-semibold text-gray-900 mb-1">Set up two-factor authentication</h1>
    <p class="text-sm text-gray-500 mb-5">
        Two-factor authentication is required for admin accounts. Scan the QR code below with an authenticator app
        (Google Authenticator, Authy, 1Password, etc.), then enter the 6-digit code to confirm.
    </p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-md p-3">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex justify-center mb-5">
        {!! $qrSvg !!}
    </div>

    <details class="mb-5 text-sm text-gray-500">
        <summary class="cursor-pointer hover:text-gray-700">Can't scan? Enter the key manually</summary>
        <p class="mt-2 font-mono tracking-widest text-gray-700 select-all">{{ $secret }}</p>
    </details>

    <form method="POST" action="{{ route('admin.totp.enroll.confirm') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="encrypted_secret" value="{{ $encryptedSecret }}">

        <div>
            <label for="code" class="block text-sm font-medium text-gray-700">Confirm with your 6-digit code</label>
            <input type="text" id="code" name="code" required inputmode="numeric" pattern="\d{6}" maxlength="6"
                   autocomplete="one-time-code"
                   class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md tracking-widest text-center text-lg"
                   placeholder="000000">
            @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-3">
            <button type="submit"
                    class="w-full px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Activate two-factor authentication
            </button>
        </div>
    </form>

    @if (auth()->user()->hasTotpEnabled())
        <div class="mt-6 pt-5 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-3">Two-factor authentication is currently active on your account.</p>
            <form method="POST" action="{{ route('admin.totp.disable') }}">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-sm text-red-600 hover:underline">
                    Disable two-factor authentication
                </button>
            </form>
        </div>
    @endif
</x-layouts.admin-auth>
