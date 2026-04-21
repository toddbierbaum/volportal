<div>
    @if ($flash)
        <div class="mb-4 px-4 py-3 rounded-md bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-900 dark:text-emerald-200">{{ $flash }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Volunteer signup</h3>
        </div>
        <div class="px-5 py-4 space-y-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model="requireApprovalBeforeOpportunities"
                       class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Require admin approval before showing opportunities</span>
                    <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        When on, new volunteers submit their info and interests, then wait for your approval before they can browse and sign up for shifts. You'll approve them from the Volunteers page, which triggers an email with a link to pick their shifts.
                    </span>
                </span>
            </label>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg flex justify-end">
            <button type="button" wire:click="save"
                    class="px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Save</button>
        </div>
    </div>
</div>
