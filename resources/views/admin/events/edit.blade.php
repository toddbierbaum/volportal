<x-layouts.admin :title="'Edit ' . $event->title . ' · Admin'">
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Events</a>
            <h1 class="mt-1 text-2xl font-bold text-fct-navy">{{ $event->title }}</h1>
            @if ($event->is_published)
                <p class="text-sm text-gray-500 mt-1">
                    Public page: <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="text-fct-navy underline">{{ route('events.show', $event->slug) }}</a>
                </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.events.duplicate', $event) }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">Duplicate</button>
            </form>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                  onsubmit="return confirm('Delete this event and all its positions and signups? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-2 text-sm rounded border border-red-300 text-red-700 bg-white hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.events.update', $event) }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
        @csrf
        @method('PUT')
        @include('admin.events._form')

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Save changes
            </button>
        </div>
    </form>

    <livewire:admin.position-editor :event="$event" />

    <div class="mt-6">
        <livewire:admin.event-signup-manager :event="$event" />
    </div>
</x-layouts.admin>
