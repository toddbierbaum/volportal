<x-layouts.public :title="$event->title">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('calendar') }}" class="inline-flex items-center text-sm text-fct-navy hover:text-fct-navy-light mb-4">
            &larr; Back to calendar
        </a>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if ($event->template)
                <div class="h-2" style="background-color: {{ $event->template->color }}"></div>
            @endif

            <div class="p-6 sm:p-8">
                @if ($event->template)
                    <span class="inline-block text-xs font-semibold uppercase tracking-wide px-2 py-1 rounded"
                          style="background-color: {{ $event->template->color }}20; color: {{ $event->template->color }}">
                        {{ $event->template->name }}
                    </span>
                @endif

                <h1 class="mt-3 text-3xl font-bold text-fct-navy">{{ $event->title }}</h1>

                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="font-medium text-gray-700">When:</span>
                        {{ $event->starts_at->format('D, M j · g:i A') }}
                        &ndash; {{ $event->ends_at->format('g:i A') }}
                    </div>
                    @if ($event->location)
                        <div class="flex items-center gap-1.5">
                            <span class="font-medium text-gray-700">Where:</span>
                            {{ $event->location }}
                        </div>
                    @endif
                </div>

                @if ($event->description)
                    <div class="mt-6 prose prose-sm max-w-none text-gray-700">
                        {{ $event->description }}
                    </div>
                @endif

                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-fct-navy mb-3">Volunteer positions</h2>

                    @if ($event->positions->isEmpty())
                        <p class="text-sm text-gray-500">No positions posted for this event yet.</p>
                    @else
                        <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                            @foreach ($event->positions as $position)
                                @php
                                    $filled = $position->signups->where('status', 'confirmed')->count();
                                    $remaining = max(0, $position->slots_needed - $filled);
                                    $isFull = $remaining === 0;
                                @endphp
                                <li class="p-4 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-gray-900">{{ $position->title }}</span>
                                            @if ($position->category)
                                                <span class="text-xs px-2 py-0.5 rounded"
                                                      style="background-color: {{ $position->category->color }}20; color: {{ $position->category->color }}">
                                                    {{ $position->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            {{ $position->starts_at->format('g:i A') }}
                                            &ndash; {{ $position->ends_at->format('g:i A') }}
                                            &middot; {{ $filled }} of {{ $position->slots_needed }} filled
                                        </div>
                                    </div>
                                    <div>
                                        @if ($isFull)
                                            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">Full &mdash; Join waitlist</span>
                                        @else
                                            <span class="text-xs px-2 py-1 rounded bg-fct-cyan-light text-fct-navy font-medium">
                                                {{ $remaining }} open
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6">
                            <a href="{{ route('signup') }}"
                               class="inline-flex items-center px-5 py-2.5 bg-fct-navy border border-transparent rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition">
                                Sign up to volunteer
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
