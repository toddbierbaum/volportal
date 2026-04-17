<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Positions</h2>
            <p class="text-sm text-gray-500 mt-0.5">Add the volunteer roles needed for this event. Use a template to prefill, or build a custom one.</p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-6 text-sm text-gray-500">No positions yet — add one below.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($positions as $position)
                    @php
                        $filled = $position->signups->where('status','confirmed')->count();
                        $waitlist = $position->signups->where('status','waitlisted')->count();
                    @endphp
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-gray-900">{{ $position->title }}</span>
                                @if ($position->category)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $position->category->color }}20; color: {{ $position->category->color }}">
                                        {{ $position->category->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $position->starts_at->format('g:i A') }}–{{ $position->ends_at->format('g:i A') }}
                                &middot; {{ $filled }}/{{ $position->slots_needed }} filled
                                @if ($waitlist > 0) &middot; {{ $waitlist }} waitlisted @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="startEdit({{ $position->id }})"
                                    class="text-sm text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="removePosition({{ $position->id }})"
                                    wire:confirm="Remove this position? Any existing signups will also be cancelled."
                                    class="text-sm text-red-600 hover:underline">Remove</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">
                {{ $editingPositionId ? 'Edit position' : 'Add position' }}
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
                    <input type="text" wire:model="title"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <label class="block text-sm font-medium text-gray-700">Slots needed</label>
                    <input type="number" min="1" max="50" wire:model="slotsNeeded"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('slotsNeeded') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-1"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Starts</label>
                    <input type="datetime-local" wire:model="startsAt"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('startsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ends</label>
                    <input type="datetime-local" wire:model="endsAt"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('endsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                @if ($editingPositionId)
                    <button type="button" wire:click="cancelEdit"
                            class="px-4 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="saveEdit"
                            class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">Save changes</button>
                @else
                    <button type="button" wire:click="addPosition"
                            class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">+ Add position</button>
                @endif
            </div>
        </div>
    </div>
</div>
