<x-layouts.public :title="'Volunteer login · ' . config('app.name')">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('calendar') }}" class="inline-flex items-center gap-1 text-sm text-fct-navy dark:text-fct-cyan hover:text-fct-navy dark:text-fct-cyan-light mb-6">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to calendar
        </a>
        <livewire:login-link-request />
    </div>
</x-layouts.public>
