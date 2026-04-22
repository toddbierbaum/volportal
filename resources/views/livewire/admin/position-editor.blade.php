<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Positions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Volunteer roles for this event.</p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">No positions yet — add one below.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($positions as $position)
                    @php
                        $filled = $position->signups->whereIn('status', ['confirmed', 'attended'])->count();
                        $waitlist = $position->signups->where('status', 'waitlisted')->count();
                        $nonCancelled = $position->signups->filter(fn ($s) => $s->status !== 'cancelled');
                        $cancelled = $position->signups->where('status', 'cancelled');
                        $color = $position->category?->color ?? '#9CA3AF';
                        $filledBadgeClasses = match (true) {
                            $filled === 0                      => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                            $filled >= $position->slots_needed => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
                            default                            => 'bg-fct-cyan/15 text-fct-navy dark:text-fct-cyan',
                        };
                    @endphp
                    <li wire:key="position-{{ $position->id }}" class="px-5 py-4 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0 mt-2" style="background-color: {{ $color }}"></span>
                                <div class="min-w-0">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $position->title }}</div>
                                    <div class="mt-1 flex items-center gap-3 flex-wrap text-sm">
                                        @if ($position->category)
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                                  style="background-color: {{ $color }}1A; color: {{ $color }}">
                                                {{ $position->category->name }}
                                            </span>
                                        @endif
                                        @if (! $position->is_public)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 dark:text-gray-500 font-medium">Admin-only</span>
                                        @endif
                                        <button type="button" wire:click="startEdit({{ $position->id }})"
                                                class="text-fct-navy dark:text-fct-cyan hover:underline">Edit</button>
                                        <button type="button" wire:click="removePosition({{ $position->id }})"
                                                wire:confirm="Remove this position? Any existing signups will also be cancelled."
                                                class="text-red-600 dark:text-red-400 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $filledBadgeClasses }}">
                                    {{ $filled }}/{{ $position->slots_needed }} filled
                                </span>
                                @if ($waitlist > 0)
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                                        {{ $waitlist }} waitlisted
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($assigningForPositionId !== $position->id)
                            <div class="mt-2 flex justify-end">
                                <button type="button" wire:click="startAssigning({{ $position->id }})"
                                        class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Assign volunteer
                                </button>
                            </div>
                        @endif

                        @if ($assigningForPositionId === $position->id)
                            <div class="mt-3 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 flex items-center gap-2 flex-wrap">
                                <select wire:model="selectedVolunteerId"
                                        class="flex-1 min-w-[200px] border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                                    <option value="">— Pick a volunteer —</option>
                                    @foreach ($availableVolunteers as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->email }})</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="assign"
                                        class="px-3 py-1.5 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Assign</button>
                                <button type="button" wire:click="cancelAssigning"
                                        class="px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">Cancel</button>
                            </div>
                        @endif

                        @if ($nonCancelled->isNotEmpty())
                            <ul class="mt-3 divide-y divide-gray-100 dark:divide-gray-700/60 border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden">
                                @foreach ($nonCancelled as $signup)
                                    <li wire:key="signup-{{ $signup->id }}" class="p-3 flex items-center justify-between gap-3 flex-wrap bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="h-8 w-8 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy dark:text-fct-cyan flex items-center justify-center font-semibold text-xs">
                                                {{ strtoupper(substr($signup->user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.volunteers.show', $signup->user_id) }}"
                                                   class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $signup->user->name }}</a>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 truncate">{{ $signup->user->email }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($isPast)
                                                <select wire:change="setStatus({{ $signup->id }}, $event.target.value)"
                                                        class="text-xs border-gray-300 dark:border-gray-600 rounded-md focus:border-fct-cyan focus:ring-fct-cyan py-1">
                                                    <option value="confirmed" @selected($signup->status === 'confirmed')>Confirmed (no status)</option>
                                                    <option value="attended" @selected($signup->status === 'attended')>Attended</option>
                                                    <option value="no_show" @selected($signup->status === 'no_show')>No-show</option>
                                                </select>
                                                @if ($signup->status === 'attended')
                                                    <input type="number" step="0.25" min="0" max="24"
                                                           value="{{ $signup->hours_worked }}"
                                                           wire:change="setHours({{ $signup->id }}, $event.target.value)"
                                                           class="w-20 text-xs border-gray-300 dark:border-gray-600 rounded-md focus:border-fct-cyan focus:ring-fct-cyan py-1"
                                                           placeholder="hrs">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">hrs</span>
                                                @endif
                                            @else
                                                @if ($signup->status === 'pending')
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium" title="Held until volunteer is approved">Queued</span>
                                                    <a href="{{ route('admin.volunteers.show', $signup->user_id) }}"
                                                       class="text-xs text-fct-navy dark:text-fct-cyan hover:underline">Review</a>
                                                @elseif ($signup->status === 'waitlisted')
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium">Waitlist</span>
                                                    <button type="button" wire:click="setStatus({{ $signup->id }}, 'confirmed')"
                                                            class="text-xs px-2 py-1 rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                                                        Promote
                                                    </button>
                                                @else
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-medium">Confirmed</span>
                                                @endif
                                            @endif

                                            <button type="button" wire:click="removeSignup({{ $signup->id }})"
                                                    wire:confirm="Remove this signup?"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">Remove</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($cancelled->isNotEmpty())
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">
                                {{ $cancelled->count() }} cancelled signup{{ $cancelled->count() === 1 ? '' : 's' }}.
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
            @if (! $editingPositionId && ! $showAddForm)
                <div class="flex justify-center">
                    <button type="button" wire:click="startAdd"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add position
                    </button>
                </div>
            @else
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                {{ $editingPositionId ? 'Edit position' : 'Add position' }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Title</label>
                    <input type="text" wire:model="title"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('title') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
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
                    <textarea wire:model="description" rows="3"
                              placeholder="e.g. Greet guests at the door, check tickets, direct them to their seats."
                              class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm"></textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Shown in reminder emails to the volunteer.</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Slots needed</label>
                    <input type="number" min="1" max="50" wire:model="slotsNeeded"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('slotsNeeded') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-start gap-2 text-sm pb-2">
                        <input type="checkbox" wire:model="isPublic"
                               class="mt-0.5 rounded-sm border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">Show on public portal</span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Uncheck to fill via admin only.</span>
                        </span>
                    </label>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Call time (minutes before event)</label>
                    <input type="number" min="0" max="1440" step="15" wire:model="callOffsetMinutes"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">0 = at event start.</p>
                    @error('callOffsetMinutes') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Duration (minutes)</label>
                    <input type="number" min="15" max="1440" step="15" wire:model="durationMinutes"
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                    @error('durationMinutes') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" wire:click="cancelEdit"
                        class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">Cancel</button>
                @if ($editingPositionId)
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
            @endif
        </div>
    </div>
</div>
