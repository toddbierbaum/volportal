<x-layouts.admin :title="'New event · Admin'">
    <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Events
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">New event</h1>
        <p class="mt-1 text-sm text-gray-500">Pick a template below to pre-fill positions and reminders, or start from scratch.</p>
    </div>

    <livewire:admin.event-wizard />
</x-layouts.admin>
