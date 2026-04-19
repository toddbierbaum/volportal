<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xs border border-gray-200 dark:border-gray-700 p-6 sm:p-8">
        @if (! $sent)
            <h2 class="text-xl font-semibold text-fct-navy dark:text-fct-cyan mb-1">Volunteer login</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 mb-6">Enter your email and we'll send you a link to log in.</p>

            <div>
                <label for="login-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" id="login-email" wire:model="email" autocomplete="email"
                       wire:keydown.enter="send"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-xs">
                @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Honeypot: hidden from humans, irresistible to bots. --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;" tabindex="-1">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" wire:model="website" autocomplete="off" tabindex="-1">
            </div>

            <div class="mt-6">
                <button type="button" wire:click="send" wire:loading.attr="disabled"
                        class="w-full inline-flex items-center justify-center px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-hidden focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="send">Send me a login link</span>
                    <span wire:loading wire:target="send">Sending…</span>
                </button>
            </div>

            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 text-center">
                Not a volunteer yet? <a href="{{ route('signup') }}" class="text-fct-navy dark:text-fct-cyan underline">Sign up here</a>.
            </p>
        @else
            <div class="text-center py-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-fct-cyan-light">
                    <svg class="h-6 w-6 text-fct-navy dark:text-fct-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-fct-navy dark:text-fct-cyan">Check your email</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500">If that email matches a volunteer account, we just sent a login link. It will be valid for 30 minutes.</p>
            </div>
        @endif
    </div>
</div>
