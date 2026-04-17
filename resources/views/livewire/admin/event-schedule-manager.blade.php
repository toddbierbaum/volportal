<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Reminder schedules for this event</h2>
            <p class="text-sm text-gray-500 mt-0.5">Add extra reminders just for this event — global defaults still apply on top.</p>
        </div>

        <div class="p-5 border-b border-gray-200 bg-gray-50">
            <h3 class="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2">Global defaults (from Admin &rsaquo; Reminders)</h3>
            @if ($globalSchedules->isEmpty())
                <p class="text-sm text-gray-500">No global schedules configured.</p>
            @else
                <ul class="text-sm text-gray-700 space-y-0.5">
                    @foreach ($globalSchedules as $s)
                        <li>&middot; {{ $s->label }} ({{ $s->offset_minutes }} min before)</li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($eventSchedules->isNotEmpty())
            <ul class="divide-y divide-gray-200">
                @foreach ($eventSchedules as $s)
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="font-medium text-gray-900">{{ $s->label }}</div>
                            <div class="text-sm text-gray-500 mt-0.5">{{ $s->offset_minutes }} minutes before event</div>
                        </div>
                        <button type="button" wire:click="delete({{ $s->id }})"
                                wire:confirm="Delete this event-specific reminder?"
                                class="text-sm text-red-600 hover:underline">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">Add event-specific reminder</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-3">
                    <label class="block text-sm font-medium text-gray-700">Label</label>
                    <input type="text" wire:model="label" placeholder="e.g. 2 hours before"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('label') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Value</label>
                    <input type="number" min="1" max="52" wire:model="offsetValue"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                    <select wire:model="offsetUnit"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                        <option value="minutes">minute(s) before the event</option>
                        <option value="hours">hour(s) before the event</option>
                        <option value="days">day(s) before the event</option>
                        <option value="weeks">week(s) before the event</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="add"
                        class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">
                    + Add reminder
                </button>
            </div>
        </div>
    </div>
</div>
