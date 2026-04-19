<x-layouts.public :title="config('app.name') . ' — Upcoming Events'">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8 flex items-end justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold text-fct-navy tracking-tight">Upcoming Events</h1>
                <p class="mt-2 text-gray-700 max-w-xl">
                    Click any event for details and to see what volunteer positions are open. New here? We'd love your help.
                </p>
            </div>
            <a href="{{ route('signup') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 focus:ring-offset-fct-cream transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Become a volunteer
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sm:p-6">
            <div id="calendar" wire:ignore></div>
        </div>

        <p class="mt-4 text-xs text-gray-500 text-center">
            Already a volunteer? <a href="{{ route('login-link') }}" class="text-fct-navy underline hover:text-fct-navy-light">Log in</a> to see your upcoming signups.
        </p>
    </div>
</x-layouts.public>
