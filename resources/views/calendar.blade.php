<x-layouts.public :title="config('app.name') . ' — Upcoming Events'">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-fct-navy">Upcoming Events</h1>
            <p class="mt-1 text-gray-700">
                Click an event to see the volunteer positions available.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div id="calendar" wire:ignore></div>
        </div>
    </div>
</x-layouts.public>
