<x-layouts.admin :title="'Event templates · Admin'">
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div class="max-w-2xl">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Event templates</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Each template captures the name, color, default positions, and default reminders for a kind of event (Kids Production, Monthly Show, etc.). Creating an event from a template auto-populates all of it.</p>
        </div>
        <a href="{{ route('admin.event-templates.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition shrink-0">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New template
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if ($templates->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">No templates yet.</div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($templates as $template)
                    @php $color = $template->color ?? '#9CA3AF'; @endphp
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="inline-block h-2.5 w-2.5 rounded-full shrink-0" style="background-color: {{ $color }}"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.event-templates.edit', $template) }}"
                                       class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $template->name }}</a>
                                    @if ($template->requires_background_check)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-300 font-medium" title="Events from this template require a volunteer background check">BG check</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                                    {{ $template->positions_count }} default position{{ $template->positions_count === 1 ? '' : 's' }}
                                    &middot; {{ $template->schedules_count }} reminder{{ $template->schedules_count === 1 ? '' : 's' }}
                                    &middot; {{ $template->events_count }} event{{ $template->events_count === 1 ? '' : 's' }} using it
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.event-templates.edit', $template) }}" class="text-sm text-fct-navy dark:text-fct-cyan hover:underline shrink-0">Edit</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
