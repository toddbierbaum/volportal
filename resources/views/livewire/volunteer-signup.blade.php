<div class="max-w-2xl mx-auto">

    {{-- Progress indicator --}}
    <ol class="flex items-center w-full mb-8 text-sm font-medium text-center text-gray-500">
        @php
            $labels = ['Your info', 'Interests', 'Opportunities', 'Done'];
        @endphp
        @foreach ($labels as $i => $label)
            @php $n = $i + 1; @endphp
            <li class="flex items-center {{ $step >= $n ? 'text-fct-navy' : '' }} {{ $n < count($labels) ? "flex-1 after:content-[''] after:w-full after:h-0.5 after:border-b after:border-1 after:inline-block after:mx-4 " . ($step > $n ? 'after:border-fct-navy' : 'after:border-gray-200') : '' }}">
                <span class="flex items-center justify-center w-7 h-7 shrink-0 rounded-full text-xs
                    {{ $step > $n ? 'bg-fct-navy text-white'
                     : ($step === $n ? 'bg-fct-navy text-white'
                     : 'bg-gray-100 text-gray-500') }}">
                    {{ $n }}
                </span>
                <span class="ml-2 hidden sm:inline">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">

        {{-- Step 1: contact info --}}
        @if ($step === 1)
            <h2 class="text-xl font-semibold text-fct-navy mb-1">Tell us about yourself</h2>
            <p class="text-sm text-gray-600 mb-6">We'll use this to match you with opportunities and send reminders.</p>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                    <input type="text" id="name" wire:model="name" autocomplete="name"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" wire:model="email" autocomplete="email"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" id="phone" wire:model="phone" autocomplete="tel"
                           class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="inline-flex items-start gap-2 text-sm">
                        <input type="checkbox" wire:model="smsOptIn"
                               class="mt-0.5 rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                        <span>
                            <span class="text-gray-700 font-medium">Also send me text reminders</span>
                            <span class="block text-xs text-gray-500">Standard message rates apply. Reply STOP to opt back out any time.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="proceedToCategories"
                        class="inline-flex items-center px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition">
                    Continue
                </button>
            </div>
        @endif

        {{-- Step 2: categories --}}
        @if ($step === 2)
            <h2 class="text-xl font-semibold text-fct-navy mb-1">How would you like to help?</h2>
            <p class="text-sm text-gray-600 mb-6">Pick the areas you're interested in. You can change these anytime.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($categories as $category)
                    <label class="relative flex items-start p-4 border rounded-lg cursor-pointer transition
                        {{ in_array($category->id, $selectedCategoryIds) ? 'border-fct-navy bg-fct-cyan-light/20 ring-1 ring-fct-navy' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="checkbox" value="{{ $category->id }}" wire:model.live="selectedCategoryIds"
                               class="mt-0.5 mr-3 rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                        <div class="flex-1 min-w-0">
                            <span class="block font-medium text-gray-900">{{ $category->name }}</span>
                            @if ($category->description)
                                <span class="block text-sm text-gray-500 mt-0.5">{{ $category->description }}</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            @error('selectedCategoryIds') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

            <div class="mt-6 flex justify-between">
                <button type="button" wire:click="backToDetails"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                    &larr; Back
                </button>
                <button type="button" wire:click="proceedToMatches"
                        class="inline-flex items-center px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition">
                    See my matches
                </button>
            </div>
        @endif

        {{-- Step 3: matches --}}
        @if ($step === 3)
            <h2 class="text-xl font-semibold text-fct-navy mb-1">Opportunities that match your interests</h2>
            <p class="text-sm text-gray-600 mb-6">
                @if ($matchedPositions->isEmpty())
                    Nothing coming up that matches your interests right now. We'll email you when something does!
                @else
                    Click <strong>Sign up</strong> on any position you'd like to take.
                @endif
            </p>

            @if ($matchedPositions->isNotEmpty())
                <ul class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                    @foreach ($matchedPositions as $position)
                        @php
                            $userSignupId = $position->signups->firstWhere('user_id', $userId)?->id;
                            $isSignedUp = $userSignupId && in_array($userSignupId, $createdSignupIds);
                            $isFull = $position->isFull();
                        @endphp
                        <li class="p-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-500">{{ $position->event->starts_at->format('D, M j · g:i A') }}</div>
                                <div class="font-medium text-gray-900">{{ $position->event->title }}</div>
                                <div class="mt-1 flex items-center gap-2 flex-wrap text-sm">
                                    <span class="text-gray-700">{{ $position->title }}</span>
                                    @if ($position->category)
                                        <span class="text-xs px-2 py-0.5 rounded"
                                              style="background-color: {{ $position->category->color }}20; color: {{ $position->category->color }}">
                                            {{ $position->category->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if ($isSignedUp)
                                    <span class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-green-50 text-green-700 font-medium">&check; Signed up</span>
                                @else
                                    <button type="button" wire:click="signUp({{ $position->id }})"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 text-sm rounded
                                                {{ $isFull ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-fct-navy text-white hover:bg-fct-navy-light' }}
                                                font-medium transition">
                                        {{ $isFull ? 'Join waitlist' : 'Sign up' }}
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="finish"
                        class="inline-flex items-center px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light focus:outline-none focus:ring-2 focus:ring-fct-cyan focus:ring-offset-2 transition">
                    {{ count($createdSignupIds) ? "I'm done" : "Finish without signing up" }}
                </button>
            </div>
        @endif

        {{-- Step 5: existing account detected, login link sent --}}
        @if ($step === 5)
            <div class="text-center py-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-fct-cyan-light">
                    <svg class="h-6 w-6 text-fct-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-fct-navy">Welcome back</h2>
                <p class="mt-2 text-sm text-gray-600">
                    That email is already registered. We sent a login link to <strong>{{ $email }}</strong> &mdash; check your inbox (valid for 30 minutes).
                </p>
                <p class="mt-3 text-sm text-gray-500">
                    Not your account? <button type="button" wire:click="backToDetails" class="text-fct-navy underline">Use a different email</button>.
                </p>
            </div>
        @endif

        {{-- Step 4: confirmation --}}
        @if ($step === 4)
            <div class="text-center py-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-fct-cyan-light">
                    <svg class="h-6 w-6 text-fct-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="mt-4 text-xl font-semibold text-fct-navy">Thanks, {{ explode(' ', $name)[0] ?: 'volunteer' }}!</h2>

                @if ($createdSignups->isNotEmpty())
                    <p class="mt-2 text-sm text-gray-600">You signed up for:</p>
                    <ul class="mt-4 text-left max-w-md mx-auto divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        @foreach ($createdSignups as $signup)
                            <li class="p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $signup->position->event->title }}</div>
                                        <div class="text-sm text-gray-600">{{ $signup->position->title }} &middot; {{ $signup->position->event->starts_at->format('D, M j · g:i A') }}</div>
                                    </div>
                                    @if ($signup->status === 'waitlisted')
                                        <span class="text-xs px-2 py-0.5 rounded bg-yellow-50 text-yellow-800">Waitlist</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded bg-green-50 text-green-700">Confirmed</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-sm text-gray-500">We'll send a reminder email before each event.</p>
                @else
                    <p class="mt-2 text-sm text-gray-600">Your interests are saved. We'll email you when matching opportunities come up.</p>
                @endif

                <div class="mt-6">
                    <a href="{{ route('calendar') }}"
                       class="inline-flex items-center px-5 py-2.5 bg-fct-navy rounded-md font-semibold text-white text-sm hover:bg-fct-navy-light transition">
                        Back to calendar
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
