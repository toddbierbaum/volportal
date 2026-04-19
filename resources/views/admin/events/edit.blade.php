<x-layouts.admin :title="'Edit ' . $event->title . ' · Admin'">
    <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Events
    </a>

    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
            @if ($event->template)
                <span class="inline-block h-3 w-3 rounded-full shrink-0" style="background-color: {{ $event->template->color }}"></span>
            @endif
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $event->title }}</h1>
                    @if (! $event->is_published)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-medium">Draft</span>
                    @endif
                </div>
                @if ($event->is_published)
                    <p class="text-sm text-gray-500 mt-1">
                        Public page:
                        <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="text-fct-navy hover:underline">
                            {{ route('events.show', $event->slug) }}
                        </a>
                    </p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <form method="POST" action="{{ route('admin.events.duplicate', $event) }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">Duplicate</button>
            </form>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                  onsubmit="return confirm('Delete this event and all its positions and signups? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 text-sm rounded-md border border-red-200 text-red-700 bg-white hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.update', $event) }}"
          class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        @csrf
        @method('PUT')
        @include('admin.events._form')

        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Save changes
            </button>
        </div>
    </form>

    <livewire:admin.position-editor :event="$event" />

    <div class="mt-6">
        <livewire:admin.event-signup-manager :event="$event" />
    </div>

    <div class="mt-6">
        <livewire:admin.event-schedule-manager :event="$event" />
    </div>
</x-layouts.admin>
