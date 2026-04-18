<div>
    @if ($flash)
        <div class="mb-4 px-4 py-3 rounded bg-green-50 border border-green-200 text-sm text-green-900">
            {{ $flash }}
        </div>
    @endif

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Categories</h2>
            <p class="text-sm text-gray-500 mt-0.5">Interest areas volunteers can select when signing up.</p>
        </div>

        @if ($items->isEmpty())
            <div class="p-6 text-sm text-gray-500">No categories yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($items as $item)
                    <li class="p-4 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded" style="background-color: {{ $item->color ?? '#999' }}"></span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                @if ($item->description)
                                    <div class="text-sm text-gray-500 mt-0.5">{{ $item->description }}</div>
                                @endif
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->event_template_positions_count }} template{{ $item->event_template_positions_count === 1 ? '' : 's' }}
                                    &middot; {{ $item->positions_count }} position{{ $item->positions_count === 1 ? '' : 's' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0 text-sm">
                            <button type="button" wire:click="startEdit({{ $item->id }})" class="text-fct-navy hover:underline">Edit</button>
                            <button type="button" wire:click="delete({{ $item->id }})"
                                    wire:confirm="Delete \"{{ $item->name }}\"?"
                                    class="text-red-600 hover:underline">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="p-5 border-t border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-fct-navy mb-3">
                {{ $editingId ? 'Edit category' : 'Add category' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Color</label>
                    <input type="color" wire:model="color"
                           class="mt-1 block h-9 w-24 border-gray-300 rounded-md">
                    @error('color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
                    <button type="button" wire:click="add" class="px-4 py-2 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">+ Add category</button>
                @endif
            </div>
        </div>
    </div>
</div>
