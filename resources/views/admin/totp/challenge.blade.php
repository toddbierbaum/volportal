<x-layouts.admin-auth title="Two-Factor Authentication">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">Two-factor authentication</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
        Enter the 6-digit code from your authenticator app to continue.
    </p>

    <form method="POST" action="{{ route('admin.totp.verify') }}" class="space-y-4">
        @csrf

        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Authentication code</label>
            <input type="text" id="code" name="code" required inputmode="numeric" pattern="\d{6}" maxlength="6"
                   autocomplete="one-time-code" autofocus
                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-fct-cyan focus:ring-fct-cyan rounded-md tracking-widest text-center text-lg"
                   placeholder="000000">
            @error('code') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="pt-3">
            <button type="submit"
                    class="w-full px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Verify
            </button>
        </div>
    </form>
</x-layouts.admin-auth>
