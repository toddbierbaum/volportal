<div>
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Reminder schedules for this event</h2>
            <p class="text-sm text-gray-500 mt-0.5">Add extra reminders just for this event — global defaults still apply on top.</p>
        </div>

        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-xs uppercase tracking-wider text-gray-500 font-semibold mb-2">Global defaults (from Admin › Reminders)</h3>
            @if ($globalSchedules->isEmpty())
                <p class="text-sm text-gray-500">No global schedules configured.</p>
            @else
                <ul class="text-sm text-gray-700 space-y-1">
                    @foreach ($globalSchedules as $s)
                        <li class="flex items-center gap-2">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-gray-400"></span>
                            {{ $s->label }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($eventSchedules->isNotEmpty())
            <ul class="divide-y divide-gray-100">
                @foreach ($eventSchedules as $s)
                    <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-2">
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-fct-cyan"></span>
                            <span class="font-medium text-gray-900">{{ $s->label }}</span>
                        </div>
                        <button type="button" wire:click="delete({{ $s->id }})"
                                wire:confirm="Delete this event-specific reminder?"
                                class="text-sm text-red-600 hover:underline">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add event-specific reminder</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Value</label>
                    <input type="number" min="1" max="52" wire:model="offsetValue"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Unit</label>
                    <select wire:model="offsetUnit"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                        <option value="minutes">minute(s) before</option>
                        <option value="hours">hour(s) before</option>
                        <option value="days">day(s) before</option>
                        <option value="weeks">week(s) before</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Channel</label>
                    <select wire:model="channel"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                        <option value="both">Email + SMS</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="add"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add reminder
                </button>
            </div>
        </div>
    </div>
</div>
