<x-layouts.public :title="'My signups · ' . config('app.name')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-fct-navy">Hi, {{ explode(' ', $user->name)[0] }}</h1>
                <p class="mt-1 text-gray-600">
                    @if ($user->categories->isNotEmpty())
                        Your interests: {{ $user->categories->pluck('name')->join(', ') }}
                    @else
                        You haven't picked any interests yet.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 hover:text-fct-navy underline">Log out</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 mb-6">
            <h2 class="text-lg font-semibold text-fct-navy mb-4">Your upcoming signups</h2>

            @if ($upcomingSignups->isEmpty())
                <p class="text-sm text-gray-500 mb-4">You're not signed up for anything yet.</p>
                <a href="{{ route('calendar') }}"
                   class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
                    Browse upcoming events
                </a>
            @else
                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    @foreach ($upcomingSignups as $signup)
                        <li class="p-4">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div>
                                    <a href="{{ route('events.show', $signup->position->event->slug) }}"
                                       class="font-medium text-gray-900 hover:text-fct-navy">
                                        {{ $signup->position->event->title }}
                                    </a>
                                    <div class="text-sm text-gray-600 mt-0.5">
                                        {{ $signup->position->title }}
                                        &middot; {{ $signup->position->event->starts_at->format('D, M j · g:i A') }}
                                    </div>
                                </div>
                                @if ($signup->status === 'waitlisted')
                                    <span class="text-xs px-2 py-1 rounded bg-yellow-50 text-yellow-800 font-medium">Waitlist</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded bg-green-50 text-green-700 font-medium">Confirmed</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($pastSignups->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-fct-navy mb-4">Past events</h2>
                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    @foreach ($pastSignups as $signup)
                        <li class="p-4 text-sm">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $signup->position->event->title }}</div>
                                    <div class="text-gray-600">
                                        {{ $signup->position->title }}
                                        &middot; {{ $signup->position->event->starts_at->format('M j, Y') }}
                                        @if ($signup->hours_worked)
                                            &middot; {{ $signup->hours_worked }} hrs
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ ucfirst($signup->status) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.public>
