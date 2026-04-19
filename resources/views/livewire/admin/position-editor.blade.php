<div>
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Positions</h2>
            <p class="text-sm text-gray-500 mt-0.5">Volunteer roles for this event.</p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">No positions yet — add one below.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($positions as $position)
                    @php
                        $filled = $position->signups->where('status','confirmed')->count();
                        $waitlist = $position->signups->where('status','waitlisted')->count();
                        $color = $position->category?->color ?? '#9CA3AF';
                    @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-gray-900">{{ $position->title }}</span>
                                    @if ($position->category)
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                              style="background-color: {{ $color }}1A; color: {{ $color }}">
                                            {{ $position->category->name }}
                                        </span>
                                    @endif
                                    @if (! $position->is_public)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">Admin-only</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 mt-0.5">
                                    {{ $position->starts_at->format('g:i A') }}–{{ $position->ends_at->format('g:i A') }}
                                    &middot; {{ $filled }}/{{ $position->slots_needed }} filled
                                    @if ($waitlist > 0) &middot; <span class="text-amber-700">{{ $waitlist }} waitlisted</span> @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 text-sm">
                            <button type="button" wire:click="startEdit({{ $position->id }})"
                                    class="text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="removePosition({{ $position->id }})"
                                    wire:confirm="Remove this position? Any existing signups will also be cancelled."
                                    class="text-red-600 hover:underline">Remove</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">
                {{ $editingPositionId ? 'Edit position' : 'Add position' }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Title</label>
                    <input type="text" wire:model="title"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Category</label>
                    <select wire:model="categoryId"
                            class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                        <option value="">— Pick one —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Description / what the volunteer does</label>
                    <textarea wire:model="description" rows="3"
                              placeholder="e.g. Greet guests at the door, check tickets, direct them to their seats."
                              class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Shown in reminder emails to the volunteer.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Slots needed</label>
                    <input type="number" min="1" max="50" wire:model="slotsNeeded"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('slotsNeeded') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-start gap-2 text-sm pb-2">
                        <input type="checkbox" wire:model="isPublic"
                               class="mt-0.5 rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 font-medium">Show on public portal</span>
                            <span class="block text-xs text-gray-500">Uncheck to fill via admin only.</span>
                        </span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Call time (minutes before event)</label>
                    <input type="number" min="0" max="1440" step="15" wire:model="callOffsetMinutes"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    <p class="mt-1 text-xs text-gray-500">0 = at event start.</p>
                    @error('callOffsetMinutes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wider">Duration (minutes)</label>
                    <input type="number" min="15" max="1440" step="15" wire:model="durationMinutes"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('durationMinutes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                @if ($editingPositionId)
                    <button type="button" wire:click="cancelEdit"
                            class="px-4 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">Cancel</button>
                    <button type="button" wire:click="saveEdit"
                            class="px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Save changes</button>
                @else
                    <button type="button" wire:click="addPosition"
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
</div>
