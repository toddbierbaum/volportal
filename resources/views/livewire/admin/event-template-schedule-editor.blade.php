<div>
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Default reminder schedules</h2>
            <p class="text-sm text-gray-500 mt-0.5">Reminders that get auto-added to every new event created from this template.</p>
        </div>

        @if ($schedules->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">No default reminders yet.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($schedules as $s)
                    <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div>
                            <div class="font-medium text-gray-900">{{ $s->label }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                                    @if ($s->channel === 'both')
                                        Email + SMS
                                    @elseif ($s->channel === 'sms')
                                        SMS
                                    @else
                                        Email
                                    @endif
                                </span>
                                @if ($s->channel !== 'email')
                                    <span class="text-gray-400">requires volunteer opt-in</span>
                                @endif
                            </div>
                        </div>
                        <button type="button" wire:click="delete({{ $s->id }})"
                                wire:confirm="Remove this default reminder?"
                                class="text-sm text-red-600 hover:underline">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add default reminder</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Send this many</label>
                    <input type="number" min="1" max="52" wire:model="offsetValue"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('offsetValue') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    Add default reminder
                </button>
            </div>
        </div>
    </div>
</div>
