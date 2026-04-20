<div>
    @if ($flash)
        <div class="mb-4 px-4 py-3 rounded-md bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-900 dark:text-emerald-200">
            {{ $flash }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        @if ($items->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">No categories yet.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($items as $item)
                    @php $color = $item->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</div>
                                    @if ($item->requires_background_check)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 font-medium" title="Volunteers must acknowledge a background check is required">BG check</span>
                                    @endif
                                    @if ($item->requires_age_certification)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium" title="Volunteers must certify they are 18+">18+</span>
                                    @endif
                                </div>
                                @if ($item->description)
                                    <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">{{ $item->description }}</div>
                                @endif
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $item->event_template_positions_count }} template{{ $item->event_template_positions_count === 1 ? '' : 's' }}
                                    &middot; {{ $item->positions_count }} position{{ $item->positions_count === 1 ? '' : 's' }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0 text-sm">
                            <button type="button" wire:click="startEdit({{ $item->id }})" class="text-fct-navy dark:text-fct-cyan hover:underline">Edit</button>
                            <button type="button" wire:click="delete({{ $item->id }})"
                                    wire:confirm="Delete &quot;{{ $item->name }}&quot;?"
                                    class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                {{ $editingId ? 'Edit category' : 'Add category' }}
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Name</label>
                    <input type="text" wire:model="name"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Color</label>
                    <input type="color" wire:model="color"
                           class="mt-1 block h-9 w-24 border border-gray-300 dark:border-gray-600 rounded-md">
                    @error('color') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Description (optional)</label>
                    <input type="text" wire:model="description"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                </div>
                <div class="sm:col-span-2 pt-2 border-t border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wider">Signup requirements</div>
                    <label class="flex items-start gap-2 text-sm cursor-pointer">
                        <input type="checkbox" wire:model="requiresBackgroundCheck"
                               class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Requires background check</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">Picking this category during signup triggers an acknowledgment screen and puts the user in pending status until an admin verifies.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 text-sm cursor-pointer">
                        <input type="checkbox" wire:model="requiresAgeCertification"
                               class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Requires 18+ certification (alcohol service)</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400">User must certify they are 18+ and the account lands in pending status for admin review.</span>
                        </span>
                    </label>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">Cancel</button>
                    <button type="button" wire:click="saveEdit" class="px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Save</button>
                @else
                    <button type="button" wire:click="add" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add category
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
