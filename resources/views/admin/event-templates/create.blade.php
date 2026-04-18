<x-layouts.admin :title="'New event template · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.event-templates.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Event templates</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">New event template</h1>
        <p class="text-sm text-gray-600 mt-1">Create the template first, then add default positions and reminders on the next screen.</p>
    </div>

    <form method="POST" action="{{ route('admin.event-templates.store') }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 max-w-2xl">
        @csrf
        @include('admin.event-templates._form')

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.event-templates.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Create template
            </button>
        </div>
    </form>
</x-layouts.admin>
