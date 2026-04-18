<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Default reminder schedules</h2>
            <p class="text-sm text-gray-500 mt-0.5">Reminders that get auto-added to every new event created from this template.</p>
        </div>

        @if ($schedules->isEmpty())
            <div class="p-6 text-sm text-gray-500">No default reminders yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($schedules as $s)
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="font-medium text-gray-900">{{ $s->label }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                via
                                @if ($s->channel === 'both')
                                    email + SMS
                                @elseif ($s->channel === 'sms')
                                    SMS
                                @else
                                    email
                                @endif
                                @if ($s->channel !== 'email')
                                    <span class="text-amber-600 ml-1">(SMS coming soon — email only for now)</span>
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

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">Add default reminder</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Send this many</label>
                    <input type="number" min="1" max="52" wire:model="offsetValue"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('offsetValue') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                    <select wire:model="offsetUnit"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                        <option value="minutes">minute(s) before</option>
                        <option value="hours">hour(s) before</option>
                        <option value="days">day(s) before</option>
                        <option value="weeks">week(s) before</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Channel</label>
                    <select wire:model="channel"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                        <option value="email">Email</option>
                        <option value="sms">SMS (coming soon)</option>
                        <option value="both">Email + SMS (coming soon)</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" wire:click="add"
                        class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">
                    + Add default reminder
                </button>
            </div>
        </div>
    </div>
</div>
