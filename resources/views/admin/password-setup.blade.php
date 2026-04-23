<x-layouts.admin title="Set Your Password">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-md">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-1">Set your password</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Choose a new password for your account.</p>

        <form method="POST" action="{{ route('admin.password-setup.submit', $admin) }}" class="space-y-4">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
            </div>

            <div class="pt-3">
                <button type="submit"
                        class="w-full px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                    Set password
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
