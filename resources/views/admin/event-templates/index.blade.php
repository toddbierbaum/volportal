<x-layouts.admin :title="'Event templates · Admin'">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-fct-navy">Event templates</h1>
            <p class="text-sm text-gray-600 mt-1">Each template captures the name, color, default positions, and default reminders for a kind of event (Kids Production, Monthly Show, etc.). Creating a new event from a template auto-populates all of it.</p>
        </div>
        <a href="{{ route('admin.event-templates.create') }}"
           class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition shrink-0">
            + New template
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        @if ($templates->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">No templates yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($templates as $template)
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="inline-block w-4 h-4 rounded" style="background-color: {{ $template->color ?? '#999' }}"></span>
                            <div>
                                <a href="{{ route('admin.event-templates.edit', $template) }}"
                                   class="font-medium text-gray-900 hover:text-fct-navy">{{ $template->name }}</a>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $template->positions_count }} default position{{ $template->positions_count === 1 ? '' : 's' }}
                                    &middot; {{ $template->schedules_count }} reminder{{ $template->schedules_count === 1 ? '' : 's' }}
                                    &middot; {{ $template->events_count }} event{{ $template->events_count === 1 ? '' : 's' }} using it
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.event-templates.edit', $template) }}" class="text-sm text-fct-navy hover:underline">Edit</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
