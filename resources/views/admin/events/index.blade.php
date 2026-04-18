<x-layouts.admin :title="'Events · Admin'">
    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-fct-navy">Events</h1>
        <a href="{{ route('admin.events.create') }}"
           class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            + New event
        </a>
    </div>

    <section class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-fct-navy uppercase tracking-wide">Upcoming</h2>
        </div>
        @if ($upcoming->isEmpty())
            <div class="p-6 text-sm text-gray-500">No upcoming events.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($upcoming as $event)
                    @php
                        $total = $event->positions->sum('slots_needed');
                        $filled = $event->positions->sum(fn ($p) => $p->signups->where('status','confirmed')->count());
                    @endphp
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.events.edit', $event) }}"
                                   class="font-medium text-gray-900 hover:text-fct-navy">{{ $event->title }}</a>
                                @if ($event->template)
                                    <span class="text-xs px-2 py-0.5 rounded"
                                          style="background-color: {{ $event->template->color }}20; color: {{ $event->template->color }}">{{ $event->template->name }}</span>
                                @endif
                                @if (! $event->is_published)
                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">Draft</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $event->starts_at->format('D, M j Y · g:i A') }}
                                @if ($event->location) &middot; {{ $event->location }} @endif
                            </div>
                        </div>
                        <div class="text-sm text-right shrink-0">
                            <div class="text-gray-900">{{ $filled }}/{{ $total }} filled</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($past->isNotEmpty())
        <section class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-sm font-semibold text-fct-navy uppercase tracking-wide">Past (recent 25)</h2>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach ($past as $event)
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('admin.events.edit', $event) }}"
                               class="font-medium text-gray-900 hover:text-fct-navy">{{ $event->title }}</a>
                            <div class="text-sm text-gray-600 mt-0.5">{{ $event->starts_at->format('D, M j Y') }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.admin>
