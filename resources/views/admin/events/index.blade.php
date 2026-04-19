<x-layouts.admin :title="'Events · Admin'">
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Events</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Create and manage theater events.</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New event
        </a>
    </div>

    <section class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Upcoming</h2>
        </div>
        @if ($upcoming->isEmpty())
            <div class="p-8 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 text-center">No upcoming events.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($upcoming as $event)
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
                                    <a href="{{ route('admin.events.edit', $event) }}"
                                       class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $event->title }}</a>
                                    @if ($event->template)
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                              style="background-color: {{ $color }}1A; color: {{ $color }}">{{ $event->template->name }}</span>
                                    @endif
                                    @if (! $event->is_published)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 dark:text-gray-500 font-medium">Draft</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $event->starts_at->format('D, M j Y · g:i A') }}
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
    </section>

    @if ($past->isNotEmpty())
        <section class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Past <span class="text-sm font-normal text-gray-500 dark:text-gray-400 dark:text-gray-500">· recent 25</span></h2>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($past as $event)
                    @php $color = $event->template?->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center gap-3 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <span class="inline-block h-2 w-2 rounded-full shrink-0 opacity-60" style="background-color: {{ $color }}"></span>
                        <a href="{{ route('admin.events.edit', $event) }}"
                           class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $event->title }}</a>
                        <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 ml-auto">{{ $event->starts_at->format('D, M j Y') }}</div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.admin>
