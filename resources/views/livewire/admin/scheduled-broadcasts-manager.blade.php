<div>
    @if ($flash)
        <div class="mb-4 px-4 py-3 rounded-md bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-900 dark:text-emerald-200">{{ $flash }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
            <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 dark:text-gray-100">Monthly opportunity alerts</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Sent on the 1st of each month to approved volunteers who opted in at signup. Lists open positions matching their interests within the next 60 days.
                    </div>
                    <div class="mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium">Email</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1.5">requires volunteer opt-in</span>
                    </div>
                </div>
                <div class="shrink-0">
                    <button type="button" wire:click="toggleOpportunityAlerts"
                            role="switch"
                            aria-checked="{{ $opportunityAlertsEnabled ? 'true' : 'false' }}"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus:outline-hidden focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2
                                {{ $opportunityAlertsEnabled ? 'bg-fct-navy' : 'bg-gray-200 dark:bg-gray-600' }}">
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition
                            {{ $opportunityAlertsEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </li>
        </ul>
    </div>
</div>
