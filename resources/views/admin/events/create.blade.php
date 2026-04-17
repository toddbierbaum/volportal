<x-layouts.admin :title="'New event · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Events</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">New event</h1>
    </div>

    <livewire:admin.event-wizard />
</x-layouts.admin>
