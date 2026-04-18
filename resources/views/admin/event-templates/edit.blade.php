<x-layouts.admin :title="'Edit ' . $template->name . ' · Admin'">
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <a href="{{ route('admin.event-templates.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Event templates</a>
            <h1 class="mt-1 text-2xl font-bold text-fct-navy">{{ $template->name }}</h1>
        </div>
        <form method="POST" action="{{ route('admin.event-templates.destroy', $template) }}"
              onsubmit="return confirm('Delete this template? Events already created from it will keep working.');"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3 py-2 text-sm rounded border border-red-300 text-red-700 bg-white hover:bg-red-50">Delete</button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.event-templates.update', $template) }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
        @csrf
        @method('PUT')
        @include('admin.event-templates._form')
        <div class="mt-6 flex justify-end">
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
