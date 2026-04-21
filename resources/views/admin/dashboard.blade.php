<x-layouts.admin :title="'Admin · ' . config('app.name')">
    @php
        $totalSlots = $upcomingEvents->sum(fn ($e) => $e->positions->sum('slots_needed'));
        $filledSlots = $upcomingEvents->sum(fn ($e) => $e->positions->sum(fn ($p) => $p->signups->where('status', 'confirmed')->count()));
        $fillPct = $totalSlots > 0 ? round(($filledSlots / $totalSlots) * 100) : 0;
    @endphp

    <div class="flex items-start justify-between gap-4 flex-wrap mb-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Next {{ $horizonDays }} days at a glance. <a href="{{ route('admin.events.index') }}" class="text-fct-navy dark:text-fct-cyan hover:underline">View full schedule →</a></p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.volunteers.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New volunteer
            </a>
            <a href="{{ route('admin.events.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New event
            </a>
        </div>
    </div>

    <div class="flex flex-wrap gap-4 mb-6">
        <a href="{{ route('admin.volunteers.index', ['status' => 'pending']) }}"
           class="flex-1 min-w-0 block bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Volunteers · pending</div>
            <div class="mt-2 text-3xl font-semibold {{ $stats['pending_review'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                {{ $stats['pending_review'] }}
            </div>
        </a>
        <div class="flex-1 min-w-0 bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Events · next {{ $horizonDays }}d</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['upcoming_events'] }}</div>
        </div>
        <div class="flex-1 min-w-0 bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Volunteers</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['volunteers'] }}</div>
        </div>
        <div class="flex-1 min-w-0 bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Signups · next {{ $horizonDays }}d</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['confirmed_signups'] }}</div>
        </div>
        <div class="flex-1 min-w-0 bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Open slots · next {{ $horizonDays }}d</div>
            <div class="mt-2 text-3xl font-semibold {{ $stats['open_slots'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                {{ $stats['open_slots'] }}
            </div>
        </div>
    </div>

    @if ($totalSlots > 0)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700 dark:text-gray-300">Upcoming slot fill</span>
                <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $filledSlots }} / {{ $totalSlots }} positions · {{ $fillPct }}%</span>
            </div>
            <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-fct-cyan transition-all" style="width: {{ $fillPct }}%"></div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Next {{ $horizonDays }} days</h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-fct-navy dark:text-fct-cyan hover:underline">View all →</a>
        </div>

        @if ($upcomingEvents->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">
                No events in the next {{ $horizonDays }} days. <a href="{{ route('admin.events.index') }}" class="text-fct-navy dark:text-fct-cyan underline">View full schedule</a>.
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($upcomingEvents as $event)
                    @php
                        $total = $event->positions->sum('slots_needed');
                        $filled = $event->positions->sum(fn ($p) => $p->signups->where('status','confirmed')->count());
                        $open = max(0, $total - $filled);
                        $color = $event->template?->color ?? '#9CA3AF';
                    @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.events.edit', $event) }}" class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $event->title }}</a>
                                    @if ($event->template)
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                              style="background-color: {{ $color }}1A; color: {{ $color }}">
                                            {{ $event->template->name }}
                                        </span>
                                    @endif
                                    @if (! $event->is_published)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 dark:text-gray-500 font-medium">Draft</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $event->starts_at->format('D, M j · g:i A') }}
                                    @if ($event->location) &middot; {{ $event->location }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-right shrink-0">
                            <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $filled }}/{{ $total }}</div>
                            @if ($open > 0)
                                <div class="text-xs text-amber-700 dark:text-amber-400">{{ $open }} open</div>
                            @else
                                <div class="text-xs text-emerald-700 dark:text-emerald-300">All filled</div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
