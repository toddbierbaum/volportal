<x-layouts.public :title="'Volunteer login · ' . config('app.name')">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('calendar') }}" class="inline-flex items-center text-sm text-fct-navy hover:text-fct-navy-light">
                &larr; Back to calendar
            </a>
        </div>
        <livewire:login-link-request />
    </div>
</x-layouts.public>
