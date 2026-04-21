<x-layouts.admin :title="$volunteer->name . ' · Admin'">
    <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-fct-navy dark:hover:text-fct-cyan mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volunteers
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0">
                <div class="h-14 w-14 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy dark:text-fct-cyan flex items-center justify-center font-semibold text-xl">
                    {{ strtoupper(substr($volunteer->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $volunteer->name }}</h1>
                        @if ($volunteer->isPendingReview())
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium">Pending review</span>
                        @endif
                    </div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 space-y-0.5">
                        <div>{{ $volunteer->email }}</div>
                        @if ($volunteer->phone)
                            <div>{{ $volunteer->phone }}</div>
                        @endif
                        <div class="text-xs text-gray-400 dark:text-gray-500">Joined {{ $volunteer->created_at->format('M j, Y') }}</div>
                    </div>
                </div>
            </div>
            <div class="flex items-start gap-6 flex-wrap">
                <div class="text-right">
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Lifetime hours</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($totalHours, 1) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium mb-1">Status</div>
                    <form method="POST" action="{{ route('admin.volunteers.status', $volunteer) }}"
                          onchange="this.submit()" class="inline">
                        @csrf
                        <select name="status"
                                class="border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm font-medium
                                       {{ $volunteer->isApproved()
                                            ? 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30'
                                            : 'text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/30' }}">
                            <option value="approved" @selected($volunteer->isApproved())>Approved</option>
                            <option value="pending" @selected($volunteer->isPendingReview())>Pending review</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $pendingSignupsCount = $upcomingSignups->where('status', 'pending')->count();
    @endphp

    @if ($volunteer->isPendingReview() && $pendingSignupsCount > 0)
        <div class="mb-6 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-lg p-4 text-sm text-amber-900 dark:text-amber-200">
            <div class="font-semibold">{{ $pendingSignupsCount }} queued signup{{ $pendingSignupsCount === 1 ? '' : 's' }} waiting on approval</div>
            <p class="mt-1 text-amber-800 dark:text-amber-300">
                They've claimed shifts that are held — no one else is blocked from those positions until this volunteer is approved. When you check the verification boxes below, queued signups get promoted to confirmed (or waitlisted if full).
            </p>
        </div>
    @endif

    {{-- Edit volunteer info --}}
    <form method="POST" action="{{ route('admin.volunteers.update', $volunteer) }}"
          class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        @csrf
        @method('PATCH')

        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Edit volunteer</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $volunteer->name) }}" required
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $volunteer->email) }}" required
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $volunteer->phone) }}"
                       placeholder="(850) 555-1234"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="inline-flex items-start gap-2 text-sm">
                    <input type="hidden" name="sms_opt_in" value="0">
                    <input type="checkbox" name="sms_opt_in" value="1"
                           @checked(old('sms_opt_in', $volunteer->sms_opt_in))
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">Opted in to SMS reminders</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Standard message rates apply. Volunteers can also toggle this themselves.</span>
                    </span>
                </label>
            </div>

            {{-- Volunteer self-attestations — display-only. Set once
                 (by the signup wizard or admin intake form) and then
                 immutable so the audit trail holds up if we ever wire
                 an automated background-check API that requires proof
                 of consent. --}}
            <div class="sm:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Volunteer self-attestations</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        What the volunteer has affirmed themselves — via the signup wizard or captured at intake. These are locked once recorded.
                    </p>
                </div>

                @php
                    $ageVia = \App\Models\User::attestationSourceLabel($volunteer->age_certified_via);
                    $bgVia = \App\Models\User::attestationSourceLabel($volunteer->background_check_acknowledged_via);
                @endphp

                <div class="flex items-start gap-3 text-sm">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-gray-900 dark:text-gray-100 font-medium">Certified 18 or older:</span>
                            @if ($volunteer->age_certified_at)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium">Certified {{ $volunteer->age_certified_at->format('M j, Y') }}@if ($ageVia) via {{ $ageVia }}@endif</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-medium">No attestation on file</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3 text-sm">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-gray-900 dark:text-gray-100 font-medium">Consented to background check:</span>
                            @if ($volunteer->background_check_acknowledged_at)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 font-medium">Consented {{ $volunteer->background_check_acknowledged_at->format('M j, Y') }}@if ($bgVia) via {{ $bgVia }}@endif</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-medium">No attestation on file</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Admin verification checkboxes — separate from user's own
                 acknowledgment timestamps. When all triggered certs are
                 verified, approved_at auto-sets and queued signups get
                 promoted. --}}
            <div class="sm:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Admin verifications</div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Check each item once you've physically verified it. When every certification the volunteer triggered is verified, their account is automatically approved.
                    </p>
                </div>

                <label class="flex items-start gap-3 text-sm cursor-pointer">
                    <input type="hidden" name="age_verified" value="0">
                    <input type="checkbox" name="age_verified" value="1"
                           @checked(old('age_verified', (bool) $volunteer->age_verified_at))
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span class="flex-1">
                        <span class="text-gray-900 dark:text-gray-100 font-medium">18+ verified</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            @if ($volunteer->age_verified_at)
                                Verified by admin on {{ $volunteer->age_verified_at->format('M j, Y') }}.
                            @else
                                Volunteer is at least 18 years old (required for serving alcohol at Concessions).
                            @endif
                        </span>
                    </span>
                </label>

                <label class="flex items-start gap-3 text-sm cursor-pointer">
                    <input type="hidden" name="background_check_verified" value="0">
                    <input type="checkbox" name="background_check_verified" value="1"
                           @checked(old('background_check_verified', (bool) $volunteer->background_check_verified_at))
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span class="flex-1">
                        <span class="text-gray-900 dark:text-gray-100 font-medium">Background check completed</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            @if ($volunteer->background_check_verified_at)
                                Verified by admin on {{ $volunteer->background_check_verified_at->format('M j, Y') }}.
                            @else
                                Background-check results received and on file (required for Kids Productions).
                            @endif
                        </span>
                    </span>
                </label>

                <div class="pt-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Current status: </span>
                    @if ($volunteer->isApproved())
                        <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300 font-medium">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            Approved ({{ $volunteer->approved_at->format('M j, Y') }})
                        </span>
                    @else
                        <span class="text-amber-700 dark:text-amber-400 font-medium">Pending review</span>
                    @endif
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interest categories</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($categories as $cat)
                        <label class="inline-flex items-center text-sm px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   @checked(in_array($cat->id, old('categories', $volunteer->categories->pluck('id')->all())))
                                   class="rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                            <span class="ml-2 flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $cat->color ?? '#9CA3AF' }}"></span>
                                {{ $cat->name }}
                                @if ($cat->requires_age_certification)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium">18+</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3">
            {{-- Delete button lives inside the update form visually but
                 submits the destroy form via the HTML5 form= attribute.
                 This avoids nested forms (where _method=DELETE would
                 piggyback onto Save and accidentally delete the user). --}}
            <button type="submit" form="destroy-volunteer-form"
                    onclick="return confirm('Delete this volunteer and all their signups? This cannot be undone.');"
                    class="px-5 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">
                Delete volunteer
            </button>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Save changes
            </button>
        </div>
    </form>

    {{-- Hidden sibling form just for DELETE. Target of the red button
         above via its form= attribute. --}}
    <form id="destroy-volunteer-form" method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <section class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Upcoming signups</h2>
        </div>
        @if ($upcomingSignups->isEmpty())
            <div class="p-8 text-sm text-gray-500 dark:text-gray-400 text-center">No upcoming signups.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($upcomingSignups as $signup)
                    @php $color = $signup->position->event->template?->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.events.edit', $signup->position->event) }}" class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:hover:text-fct-cyan">
                                    {{ $signup->position->event->title }}
                                </a>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $signup->position->title }}
                                    &middot; {{ $signup->position->event->starts_at->format('D, M j · g:i A') }}
                                </div>
                            </div>
                        </div>
                        @if ($signup->status === 'pending')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium shrink-0" title="Queued — will be assigned once volunteer is approved">Queued</span>
                        @elseif ($signup->status === 'waitlisted')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 font-medium shrink-0">Waitlist</span>
                        @elseif ($signup->status === 'cancelled' || $signup->status === 'canceled')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium shrink-0">Cancelled</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-medium shrink-0">Confirmed</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($pastSignups->isNotEmpty())
        <section class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">History</h2>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($pastSignups as $signup)
                    @php $color = $signup->position->event->template?->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2 w-2 rounded-full shrink-0 opacity-60" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $signup->position->event->title }}</div>
                                <div class="text-gray-500 dark:text-gray-400">
                                    {{ $signup->position->title }}
                                    &middot; {{ $signup->position->event->starts_at->format('M j, Y') }}
                                    @if ($signup->hours_worked) &middot; {{ $signup->hours_worked }} hrs @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium shrink-0">{{ ucfirst(str_replace('_', ' ', $signup->status)) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.admin>
