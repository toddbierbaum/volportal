<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Start from a template</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-4">Pick a template to auto-populate default positions and reminders. You can tweak everything before saving.</p>
        <select wire:model.live="eventTemplateId"
                class="block w-full sm:max-w-md border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
            <option value="">— None (blank event) —</option>
            @foreach ($templates as $template)
                <option value="{{ $template->id }}">{{ $template->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Event details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Title</label>
                <input type="text" wire:model="title"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Location</label>
                <input type="text" wire:model="location"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
            </div>

            <div></div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Starts at</label>
                <input type="datetime-local" wire:model.live="startsAt"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('startsAt') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Ends at</label>
                <input type="datetime-local" wire:model.live="endsAt"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('endsAt') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Description</label>
                <textarea wire:model="description" rows="3"
                          class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="isPublished"
                           class="rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Published (visible to volunteers)</span>
                </label>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Positions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                @if ($eventTemplateId)
                    Pre-filled from the template. Tweak or add more below.
                @else
                    Add the volunteer roles needed for this event.
                @endif
            </p>
        </div>

        @if (empty($draftPositions))
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">No positions yet — add one below.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($draftPositions as $i => $pos)
                    @php
                        $category = $categories->firstWhere('id', $pos['categoryId']);
                        $color = $category?->color ?? '#9CA3AF';
                    @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $pos['title'] }}</span>
                                    @if ($category)
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                              style="background-color: {{ $color }}1A; color: {{ $color }}">
                                            {{ $category->name }}
                                        </span>
                                    @endif
                                    @if (! ($pos['isPublic'] ?? true))
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 dark:text-gray-500 font-medium">Admin-only</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                                    Call {{ $pos['callOffsetMinutes'] ?? 60 }} min before event
                                    &middot; {{ $pos['durationMinutes'] ?? 180 }} min duration
                                    &middot; {{ $pos['slotsNeeded'] }} slot{{ $pos['slotsNeeded'] === 1 ? '' : 's' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 text-sm">
                            <button type="button" wire:click="editDraftPosition({{ $i }})" class="text-fct-navy dark:text-fct-cyan hover:underline">Edit</button>
                            <button type="button" wire:click="removeDraftPosition({{ $i }})" class="text-red-600 dark:text-red-400 hover:underline">Remove</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                {{ $editingIndex !== null ? 'Edit position' : 'Add a position' }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Title</label>
                    <input type="text" wire:model="positionTitle"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('positionTitle') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Category</label>
                    <select wire:model="categoryId"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                        <option value="">— Pick one —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Description / what the volunteer does</label>
                    <textarea wire:model="positionDescription" rows="2"
                              placeholder="e.g. Greet guests at the door, check tickets, direct them to their seats."
                              class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Slots</label>
                    <input type="number" min="1" max="50" wire:model="positionSlots"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('positionSlots') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-start gap-2 text-sm pb-2">
                        <input type="checkbox" wire:model="positionIsPublic"
                               class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Show on public portal</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Uncheck to fill via admin only.</span>
                        </span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Call time (min before event)</label>
                    <input type="number" min="0" max="1440" step="15" wire:model="positionCallOffsetMinutes"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('positionCallOffsetMinutes') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Duration (min)</label>
                    <input type="number" min="15" max="1440" step="15" wire:model="positionDurationMinutes"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('positionDurationMinutes') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                @if ($editingIndex !== null)
                    <button type="button" wire:click="cancelEditDraftPosition"
                            class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="button" wire:click="saveEditedPosition"
                            class="px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Save changes</button>
                @else
                    <button type="button" wire:click="addDraftPosition"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add position
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if (! empty($draftSchedules))
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Reminders (from template)</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">These will be set up on the event. You can add or remove reminders after saving.</p>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($draftSchedules as $s)
                    <li class="px-5 py-3 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-fct-cyan"></span>
                        {{ $s['label'] }}
                        @if ($s['channel'] !== 'email')
                            <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 ml-2">via {{ $s['channel'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.events.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100">Cancel</a>
        <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2.5 bg-fct-navy text-white rounded-md text-sm font-semibold hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Create event</span>
            <span wire:loading wire:target="save">Creating…</span>
        </button>
    </div>
</div>
