<x-layouts.admin :title="'Edit ' . $template->name . ' · Admin'">
    <a href="{{ route('admin.event-templates.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Event templates
    </a>

    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
            <span class="inline-block h-3 w-3 rounded-full shrink-0" style="background-color: {{ $template->color ?? '#9CA3AF' }}"></span>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $template->name }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.event-templates.destroy', $template) }}"
              onsubmit="return confirm('Delete this template? Events already created from it will keep working.');"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3 py-2 text-sm rounded-md border border-red-200 text-red-700 bg-white hover:bg-red-50">Delete</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.event-templates.update', $template) }}"
          class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        @csrf
        @method('PUT')
        @include('admin.event-templates._form')
        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Save basics
            </button>
        </div>
    </form>

    <div class="mb-6">
        <livewire:admin.event-template-position-editor :template="$template" />
    </div>

    <div>
        <livewire:admin.event-template-schedule-editor :template="$template" />
    </div>
</x-layouts.admin>
