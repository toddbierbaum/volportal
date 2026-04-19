<x-layouts.admin :title="'New event template · Admin'">
    <a href="{{ route('admin.event-templates.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-fct-navy dark:text-fct-cyan mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Event templates
    </a>

    <div class="mb-6 max-w-2xl">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">New event template</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Create the template first, then add default positions and reminders on the next screen.</p>
    </div>

    <form method="POST" action="{{ route('admin.event-templates.store') }}"
          class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
        @csrf
        @include('admin.event-templates._form')

        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3">
            <a href="{{ route('admin.event-templates.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Create template
            </button>
        </div>
    </form>
</x-layouts.admin>
