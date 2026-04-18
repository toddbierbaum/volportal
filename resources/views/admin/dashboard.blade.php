<x-layouts.admin :title="'Admin · ' . config('app.name')">
    <h1 class="text-2xl font-bold text-fct-navy mb-6">Dashboard</h1>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="text-sm text-gray-500">Upcoming events</div>
            <div class="mt-1 text-3xl font-semibold text-fct-navy">{{ $stats['upcoming_events'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="text-sm text-gray-500">Volunteers</div>
            <div class="mt-1 text-3xl font-semibold text-fct-navy">{{ $stats['volunteers'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="text-sm text-gray-500">Confirmed signups</div>
            <div class="mt-1 text-3xl font-semibold text-fct-navy">{{ $stats['confirmed_signups'] }}</div>
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            <div class="text-sm text-gray-500">Open slots</div>
            <div class="mt-1 text-3xl font-semibold {{ $stats['open_slots'] > 0 ? 'text-amber-600' : 'text-fct-navy' }}">
                {{ $stats['open_slots'] }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200 flex items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-fct-navy">Upcoming events</h2>
            <a href="{{ route('admin.events.create') }}"
               class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
                + New event
            </a>
        </div>

        @if ($upcomingEvents->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">
                No upcoming events. <a href="{{ route('admin.events.create') }}" class="text-fct-navy underline">Create one</a>.
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($upcomingEvents as $event)
                    @php
                        $total = $event->positions->sum('slots_needed');
                        $filled = $event->positions->sum(fn ($p) => $p->signups->where('status','confirmed')->count());
                        $open = max(0, $total - $filled);
                    @endphp
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.events.edit', $event) }}" class="font-medium text-gray-900 hover:text-fct-navy">{{ $event->title }}</a>
                                @if ($event->template)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $event->template->color }}20; color: {{ $event->template->color }}">
                                        {{ $event->template->name }}
                                    </span>
                                @endif
                                @if (! $event->is_published)
                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">Draft</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $event->starts_at->format('D, M j · g:i A') }}
                                @if ($event->location) &middot; {{ $event->location }} @endif
                            </div>
                        </div>
                        <div class="text-sm text-right shrink-0">
                            <div class="text-gray-900 font-medium">{{ $filled }}/{{ $total }} filled</div>
                            @if ($open > 0)
                                <div class="text-xs text-amber-700">{{ $open }} open</div>
                            @else
                                <div class="text-xs text-green-700">All filled</div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
