<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Default positions</h2>
            <p class="text-sm text-gray-500 mt-0.5">Positions that get auto-added to every new event created from this template. Admins can still add, edit, or remove positions on an individual event afterwards.</p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-6 text-sm text-gray-500">No default positions yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($positions as $p)
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-gray-900">{{ $p->title }}</span>
                                @if ($p->category)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $p->category->color }}20; color: {{ $p->category->color }}">
                                        {{ $p->category->name }}
                                    </span>
                                @endif
                                @if (! $p->is_public)
                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">Admin-only</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $p->slots_needed }} slot{{ $p->slots_needed === 1 ? '' : 's' }}
                                &middot; Call {{ $p->call_offset_minutes }} min before event
                                &middot; {{ $p->duration_minutes }} min duration
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 text-sm">
                            <button type="button" wire:click="startEdit({{ $p->id }})" class="text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="delete({{ $p->id }})"
                                    wire:confirm="Remove this default position?"
                                    class="text-red-600 hover:underline">Remove</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">
                {{ $editingId ? 'Edit default position' : 'Add default position' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
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
                    <label class="block text-sm font-medium text-gray-700">Call offset (min before event)</label>
                    <input type="number" min="0" max="1440" wire:model="callOffsetMinutes"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('callOffsetMinutes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Duration (min)</label>
                    <input type="number" min="15" max="1440" wire:model="durationMinutes"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('durationMinutes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="px-4 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="saveEdit" class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">Save</button>
                @else
                    <button type="button" wire:click="add" class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">+ Add default position</button>
                @endif
            </div>
        </div>
    </div>
</div>
