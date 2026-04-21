<x-layouts.public :title="'Sign up to volunteer · ' . config('app.name')">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @unless (\App\Models\Setting::get('require_approval_before_opportunities', false))
            <a href="{{ route('calendar') }}" class="inline-flex items-center gap-1 text-sm text-fct-navy dark:text-fct-cyan hover:text-fct-navy dark:text-fct-cyan-light mb-4">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to calendar
            </a>
        @endunless
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-fct-navy dark:text-fct-cyan tracking-tight">Become a volunteer</h1>
            <p class="mt-2 text-gray-700 dark:text-gray-300 max-w-xl">Three quick steps and we'll match you with upcoming opportunities at the theater.</p>
        </div>

        <livewire:volunteer-signup />
    </div>
</x-layouts.public>
