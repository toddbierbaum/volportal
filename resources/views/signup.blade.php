<x-layouts.public :title="'Sign up to volunteer · ' . config('app.name')">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('calendar') }}" class="inline-flex items-center text-sm text-fct-navy hover:text-fct-navy-light">
                &larr; Back to calendar
            </a>
            <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-fct-navy">Become a volunteer</h1>
            <p class="mt-1 text-gray-600">Three quick steps and we'll match you with upcoming opportunities.</p>
        </div>

        <livewire:volunteer-signup />
    </div>
</x-layouts.public>
