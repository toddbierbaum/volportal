<x-layouts.admin :title="'New event · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Events</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">New event</h1>
    </div>

    <form method="POST" action="{{ route('admin.events.store') }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
        @csrf
        @include('admin.events._form')

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.events.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Create event
            </button>
        </div>
    </form>
</x-layouts.admin>
