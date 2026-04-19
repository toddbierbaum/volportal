<x-layouts.admin :title="$volunteer->name . ' · Admin'">
    <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volunteers
    </a>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4 min-w-0">
                <div class="h-14 w-14 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy flex items-center justify-center font-semibold text-xl">
                    {{ strtoupper(substr($volunteer->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $volunteer->name }}</h1>
                    <div class="mt-1 text-sm text-gray-600 space-y-0.5">
                        <div>{{ $volunteer->email }}</div>
                        @if ($volunteer->phone)
                            <div>{{ $volunteer->phone }}</div>
                        @endif
                        <div class="text-xs text-gray-400">Joined {{ $volunteer->created_at->format('M j, Y') }}</div>
                    </div>
                    @if ($volunteer->categories->isNotEmpty())
                        <div class="mt-3 flex items-center gap-1 flex-wrap">
                            @foreach ($volunteer->categories as $cat)
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                      style="background-color: {{ $cat->color }}1A; color: {{ $cat->color }}">
                                    {{ $cat->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 uppercase tracking-wider font-medium">Lifetime hours</div>
                <div class="text-3xl font-semibold text-gray-900 mt-1">{{ number_format($totalHours, 1) }}</div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}"
                  onsubmit="return confirm('Delete this volunteer and all their signups? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-700">Delete volunteer</button>
            </form>
        </div>
    </div>

    <section class="bg-white rounded-lg border border-gray-200 mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Upcoming signups</h2>
        </div>
        @if ($upcomingSignups->isEmpty())
            <div class="p-8 text-sm text-gray-500 text-center">No upcoming signups.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($upcomingSignups as $signup)
                    @php $color = $signup->position->event->template?->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.events.edit', $signup->position->event) }}" class="font-medium text-gray-900 hover:text-fct-navy">
                                    {{ $signup->position->event->title }}
                                </a>
                                <div class="text-sm text-gray-500 mt-0.5">
                                    {{ $signup->position->title }}
                                    &middot; {{ $signup->position->event->starts_at->format('D, M j · g:i A') }}
                                </div>
                            </div>
                        </div>
                        @if ($signup->status === 'waitlisted')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-medium shrink-0">Waitlist</span>
                        @elseif ($signup->status === 'cancelled' || $signup->status === 'canceled')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium shrink-0">Cancelled</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium shrink-0">Confirmed</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($pastSignups->isNotEmpty())
        <section class="bg-white rounded-lg border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">History</h2>
            </div>
            <ul class="divide-y divide-gray-100">
                @foreach ($pastSignups as $signup)
                    @php $color = $signup->position->event->template?->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 text-sm hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2 w-2 rounded-full shrink-0 opacity-60" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="font-medium text-gray-900">{{ $signup->position->event->title }}</div>
                                <div class="text-gray-500">
                                    {{ $signup->position->title }}
                                    &middot; {{ $signup->position->event->starts_at->format('M j, Y') }}
                                    @if ($signup->hours_worked) &middot; {{ $signup->hours_worked }} hrs @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium shrink-0">{{ ucfirst(str_replace('_', ' ', $signup->status)) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.admin>
