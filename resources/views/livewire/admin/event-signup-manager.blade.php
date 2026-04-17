<div>
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Volunteer signups</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                @if ($isPast)
                    Mark attendance and record hours for each signup below.
                @else
                    Assign volunteers directly or manage who's already signed up.
                @endif
            </p>
        </div>

        @if ($positions->isEmpty())
            <div class="p-6 text-sm text-gray-500">No positions on this event yet — add some above.</div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach ($positions as $position)
                    @php
                        $confirmed = $position->signups->where('status','confirmed');
                        $waitlisted = $position->signups->where('status','waitlisted');
                        $attended = $position->signups->where('status','attended');
                        $noShow = $position->signups->where('status','no_show');
                        $cancelled = $position->signups->where('status','cancelled');
                        $nonCancelled = $position->signups->filter(fn ($s) => $s->status !== 'cancelled');
                    @endphp
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-900">{{ $position->title }}</span>
                                    @if ($position->category)
                                        <span class="text-xs px-2 py-0.5 rounded"
                                              style="background-color: {{ $position->category->color }}20; color: {{ $position->category->color }}">
                                            {{ $position->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600 mt-0.5">
                                    {{ $position->starts_at->format('g:i A') }}–{{ $position->ends_at->format('g:i A') }}
                                    &middot; {{ $confirmed->count() + $attended->count() }}/{{ $position->slots_needed }} filled
                                </div>
                            </div>
                            @if ($assigningForPositionId !== $position->id)
                                <button type="button" wire:click="startAssigning({{ $position->id }})"
                                        class="text-sm px-3 py-1.5 rounded border border-gray-300 bg-white hover:bg-gray-50">
                                    + Assign volunteer
                                </button>
                            @endif
                        </div>

                        @if ($assigningForPositionId === $position->id)
                            <div class="mt-3 p-3 rounded bg-gray-50 border border-gray-200 flex items-center gap-2 flex-wrap">
                                <select wire:model="selectedVolunteerId"
                                        class="flex-1 min-w-[200px] border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
                                    <option value="">— Pick a volunteer —</option>
                                    @foreach ($availableVolunteers as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->email }})</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="assign"
                                        class="px-3 py-1.5 text-sm rounded bg-fct-navy text-white hover:bg-fct-navy-light">Assign</button>
                                <button type="button" wire:click="cancelAssigning"
                                        class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Cancel</button>
                            </div>
                        @endif

                        @if ($nonCancelled->isEmpty())
                            <div class="mt-3 text-sm text-gray-500">No signups yet.</div>
                        @else
                            <ul class="mt-3 divide-y divide-gray-100 border border-gray-200 rounded">
                                @foreach ($nonCancelled as $signup)
                                    <li class="p-3 flex items-center justify-between gap-3 flex-wrap">
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.volunteers.show', $signup->user_id) }}"
                                               class="font-medium text-gray-900 hover:text-fct-navy">{{ $signup->user->name }}</a>
                                            <div class="text-xs text-gray-500">{{ $signup->user->email }}</div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($isPast)
                                                <select wire:change="setStatus({{ $signup->id }}, $event.target.value)"
                                                        class="text-xs border-gray-300 rounded focus:border-fct-cyan focus:ring-fct-cyan py-1">
                                                    <option value="confirmed" @selected($signup->status === 'confirmed')>Confirmed (no status)</option>
                                                    <option value="attended" @selected($signup->status === 'attended')>Attended</option>
                                                    <option value="no_show" @selected($signup->status === 'no_show')>No-show</option>
                                                </select>
                                                @if ($signup->status === 'attended')
                                                    <input type="number" step="0.25" min="0" max="24"
                                                           value="{{ $signup->hours_worked }}"
                                                           wire:change="setHours({{ $signup->id }}, $event.target.value)"
                                                           class="w-20 text-xs border-gray-300 rounded focus:border-fct-cyan focus:ring-fct-cyan py-1"
                                                           placeholder="hrs">
                                                    <span class="text-xs text-gray-500">hrs</span>
                                                @endif
                                            @else
                                                @if ($signup->status === 'waitlisted')
                                                    <span class="text-xs px-2 py-1 rounded bg-yellow-50 text-yellow-800 font-medium">Waitlist</span>
                                                    <button type="button" wire:click="setStatus({{ $signup->id }}, 'confirmed')"
                                                            class="text-xs px-2 py-1 rounded bg-fct-navy text-white hover:bg-fct-navy-light">
                                                        Promote
                                                    </button>
                                                @else
                                                    <span class="text-xs px-2 py-1 rounded bg-green-50 text-green-700 font-medium">Confirmed</span>
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
