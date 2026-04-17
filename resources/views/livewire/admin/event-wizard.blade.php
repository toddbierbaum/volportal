<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-fct-navy mb-4">Event details</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" wire:model="title"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Event type</label>
                <select wire:model="eventTypeId"
                        class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                    <option value="">—</option>
                    @foreach ($eventTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" wire:model="location"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Starts at</label>
                <input type="datetime-local" wire:model.live="startsAt"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('startsAt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Ends at</label>
                <input type="datetime-local" wire:model.live="endsAt"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('endsAt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model="description" rows="3"
                          class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="isPublished"
                           class="rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                    <span class="ml-2 text-sm text-gray-700">Published (visible to volunteers)</span>
                </label>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Positions</h2>
            <p class="text-sm text-gray-500 mt-0.5">Add the volunteer roles needed. Pick a template to prefill, or build a custom one.</p>
        </div>

        @if (empty($draftPositions))
            <div class="p-6 text-sm text-gray-500">No positions yet — add one below.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($draftPositions as $i => $pos)
                    @php
                        $category = $categories->firstWhere('id', $pos['categoryId']);
                        $startStr = \Carbon\Carbon::parse($pos['startsAt'])->format('g:i A');
                        $endStr = \Carbon\Carbon::parse($pos['endsAt'])->format('g:i A');
                    @endphp
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-gray-900">{{ $pos['title'] }}</span>
                                @if ($category)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $category->color }}20; color: {{ $category->color }}">
                                        {{ $category->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $startStr }}–{{ $endStr }} · {{ $pos['slotsNeeded'] }} slot{{ $pos['slotsNeeded'] === 1 ? '' : 's' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="editDraftPosition({{ $i }})" class="text-sm text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="removeDraftPosition({{ $i }})" class="text-sm text-red-600 hover:underline">Remove</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">
                {{ $editingIndex !== null ? 'Edit position' : 'Add a position' }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Template (optional)</label>
                    <select wire:model.live="templateId"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                        <option value="">— None (custom) —</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->title }} ({{ $template->category?->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" wire:model="positionTitle"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('positionTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select wire:model="categoryId"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                        <option value="">— Pick one —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slots</label>
                    <input type="number" min="1" max="50" wire:model="positionSlots"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('positionSlots') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Starts</label>
                    <input type="datetime-local" wire:model="positionStartsAt"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('positionStartsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ends</label>
                    <input type="datetime-local" wire:model="positionEndsAt"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('positionEndsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                @if ($editingIndex !== null)
                    <button type="button" wire:click="cancelEditDraftPosition"
                            class="px-4 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="saveEditedPosition"
                            class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">Save changes</button>
                @else
                    <button type="button" wire:click="addDraftPosition"
                            class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">+ Add position</button>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.events.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="px-6 py-2.5 bg-fct-navy text-white rounded-md text-sm font-semibold hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Create event</span>
            <span wire:loading wire:target="save">Creating…</span>
        </button>
    </div>
</div>
