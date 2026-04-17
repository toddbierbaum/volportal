<div>
    @if ($flash)
        <div class="mb-4 px-4 py-3 rounded bg-green-50 border border-green-200 text-sm text-green-900">{{ $flash }}</div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Position templates</h2>
            <p class="text-sm text-gray-500 mt-0.5">Reusable role definitions you can drop into events (House Manager, Concessions, etc.).</p>
        </div>

        @if ($items->isEmpty())
            <div class="p-6 text-sm text-gray-500">No templates yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($items as $item)
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-gray-900">{{ $item->title }}</span>
                                @if ($item->category)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $item->category->color }}20; color: {{ $item->category->color }}">
                                        {{ $item->category->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500 mt-0.5">
                                @if ($item->default_duration_minutes)
                                    Default duration: {{ $item->default_duration_minutes }} min
                                @endif
                                @if ($item->description) &middot; {{ $item->description }} @endif
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Used by {{ $item->positions_count }} position{{ $item->positions_count === 1 ? '' : 's' }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 text-sm">
                            <button type="button" wire:click="startEdit({{ $item->id }})" class="text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="delete({{ $item->id }})"
                                    wire:confirm="Delete \"{{ $item->title }}\" template? Existing positions using it will keep working."
                                    class="text-red-600 hover:underline">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">
                {{ $editingId ? 'Edit template' : 'Add template' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
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
                    <label class="block text-sm font-medium text-gray-700">Default duration (min)</label>
                    <input type="number" min="1" max="1440" wire:model="defaultDurationMinutes"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('defaultDurationMinutes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description (optional)</label>
                    <input type="text" wire:model="description"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="px-4 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="saveEdit" class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">Save</button>
                @else
                    <button type="button" wire:click="add" class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">+ Add template</button>
                @endif
            </div>
        </div>
    </div>
</div>
