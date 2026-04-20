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

        @if (session('status'))
            <div class="mb-4 px-4 py-3 rounded-sm bg-green-50 border border-green-200 text-sm text-green-900">
                {{ session('status') }}
            </div>
        @endif

        <details class="bg-white rounded-lg shadow-xs border border-gray-200 mb-6">
            <summary class="p-6 sm:p-8 cursor-pointer list-none flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-fct-navy">Reminder preferences</h2>
                    <p class="text-sm text-gray-600 mt-0.5">
                        You currently receive reminders via <strong>email</strong>
                        @if ($user->sms_opt_in && $user->phone)
                            and <strong>text</strong> to {{ $user->phone }}
                        @endif.
                    </p>
                </div>
                <span class="text-fct-navy text-sm">Edit &rsaquo;</span>
            </summary>
            <form method="POST" action="{{ route('volunteer.preferences') }}" class="px-6 sm:px-8 pb-6 sm:pb-8 border-t border-gray-100 pt-4 space-y-4">
                @csrf
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone (for text reminders)</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel"
                           class="mt-1 block w-full sm:max-w-sm border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-xs">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="inline-flex items-start gap-2 text-sm">
                        <input type="hidden" name="sms_opt_in" value="0">
                        <input type="checkbox" name="sms_opt_in" value="1" @checked(old('sms_opt_in', $user->sms_opt_in))
                               class="mt-0.5 rounded-sm border-gray-300 text-fct-navy focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 font-medium">Send me text reminders</span>
                            <span class="block text-xs text-gray-500">Standard message rates apply. Reply STOP to any text to opt back out.</span>
                        </span>
                    </label>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">Save preferences</button>
                </div>
            </form>
        </details>

        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 sm:p-8 mb-6">
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
                                @if ($signup->status === 'pending')
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-800 font-medium" title="We'll confirm your spot once you're approved">Queued</span>
                                @elseif ($signup->status === 'waitlisted')
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-800 font-medium">Waitlist</span>
                                @else
                                    <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium">Confirmed</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($pastSignups->isNotEmpty())
            <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 sm:p-8">
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
                                <span class="text-xs px-2 py-0.5 rounded-sm bg-gray-100 text-gray-700">{{ ucfirst($signup->status) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.public>
