<div>
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Volunteer signups</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                @if ($isPast)
                    Mark attendance and record hours for each signup below.
                @else
                    Assign volunteers directly or manage who's already signed up.
                @endif
            </p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-8 text-center text-sm text-gray-500">No positions on this event yet — add some above.</div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach ($positions as $position)
                    @php
                        $confirmed = $position->signups->where('status','confirmed');
                        $waitlisted = $position->signups->where('status','waitlisted');
                        $attended = $position->signups->where('status','attended');
                        $noShow = $position->signups->where('status','no_show');
                        $cancelled = $position->signups->where('status','cancelled');
                        $nonCancelled = $position->signups->filter(fn ($s) => $s->status !== 'cancelled');
                        $color = $position->category?->color ?? '#9CA3AF';
                    @endphp
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-semibold text-gray-900">{{ $position->title }}</span>
                                        @if ($position->category)
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                                  style="background-color: {{ $color }}1A; color: {{ $color }}">
                                                {{ $position->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 mt-0.5">
                                        {{ $position->starts_at->format('g:i A') }}–{{ $position->ends_at->format('g:i A') }}
                                        &middot; {{ $confirmed->count() + $attended->count() }}/{{ $position->slots_needed }} filled
                                    </div>
                                </div>
                            </div>
                            @if ($assigningForPositionId !== $position->id)
                                <button type="button" wire:click="startAssigning({{ $position->id }})"
                                        class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Assign volunteer
                                </button>
                            @endif
                        </div>

                        @if ($assigningForPositionId === $position->id)
                            <div class="mt-3 p-3 rounded-md bg-gray-50 border border-gray-200 flex items-center gap-2 flex-wrap">
                                <select wire:model="selectedVolunteerId"
                                        class="flex-1 min-w-[200px] border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
                                    <option value="">— Pick a volunteer —</option>
                                    @foreach ($availableVolunteers as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->email }})</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="assign"
                                        class="px-3 py-1.5 text-sm rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">Assign</button>
                                <button type="button" wire:click="cancelAssigning"
                                        class="px-3 py-1.5 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">Cancel</button>
                            </div>
                        @endif

                        @if ($nonCancelled->isEmpty())
                            <div class="mt-3 text-sm text-gray-500">No signups yet.</div>
                        @else
                            <ul class="mt-3 divide-y divide-gray-100 border border-gray-200 rounded-md overflow-hidden">
                                @foreach ($nonCancelled as $signup)
                                    <li class="p-3 flex items-center justify-between gap-3 flex-wrap bg-white hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="h-8 w-8 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy flex items-center justify-center font-semibold text-xs">
                                                {{ strtoupper(substr($signup->user->name, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.volunteers.show', $signup->user_id) }}"
                                                   class="font-medium text-gray-900 hover:text-fct-navy">{{ $signup->user->name }}</a>
                                                <div class="text-xs text-gray-500 truncate">{{ $signup->user->email }}</div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($isPast)
                                                <select wire:change="setStatus({{ $signup->id }}, $event.target.value)"
                                                        class="text-xs border-gray-300 rounded-md focus:border-fct-cyan focus:ring-fct-cyan py-1">
                                                    <option value="confirmed" @selected($signup->status === 'confirmed')>Confirmed (no status)</option>
                                                    <option value="attended" @selected($signup->status === 'attended')>Attended</option>
                                                    <option value="no_show" @selected($signup->status === 'no_show')>No-show</option>
                                                </select>
                                                @if ($signup->status === 'attended')
                                                    <input type="number" step="0.25" min="0" max="24"
                                                           value="{{ $signup->hours_worked }}"
                                                           wire:change="setHours({{ $signup->id }}, $event.target.value)"
                                                           class="w-20 text-xs border-gray-300 rounded-md focus:border-fct-cyan focus:ring-fct-cyan py-1"
                                                           placeholder="hrs">
                                                    <span class="text-xs text-gray-500">hrs</span>
                                                @endif
                                            @else
                                                @if ($signup->status === 'waitlisted')
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-medium">Waitlist</span>
                                                    <button type="button" wire:click="setStatus({{ $signup->id }}, 'confirmed')"
                                                            class="text-xs px-2 py-1 rounded-md bg-fct-navy text-white hover:bg-fct-navy-light font-medium">
                                                        Promote
                                                    </button>
                                                @else
                                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">Confirmed</span>
                                                @endif
                                            @endif

                                            <button type="button" wire:click="removeSignup({{ $signup->id }})"
                                                    wire:confirm="Remove this signup?"
                                                    class="text-xs text-red-600 hover:underline">Remove</button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($cancelled->isNotEmpty())
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $cancelled->count() }} cancelled signup{{ $cancelled->count() === 1 ? '' : 's' }}.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
