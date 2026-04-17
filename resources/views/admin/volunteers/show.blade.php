<x-layouts.admin :title="$volunteer->name . ' · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.volunteers.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Volunteers</a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-fct-navy">{{ $volunteer->name }}</h1>
                <div class="mt-2 text-sm text-gray-700 space-y-0.5">
                    <div><span class="text-gray-500">Email:</span> {{ $volunteer->email }}</div>
                    @if ($volunteer->phone)
                        <div><span class="text-gray-500">Phone:</span> {{ $volunteer->phone }}</div>
                    @endif
                    <div><span class="text-gray-500">Joined:</span> {{ $volunteer->created_at->format('M j, Y') }}</div>
                </div>
                @if ($volunteer->categories->isNotEmpty())
                    <div class="mt-3 flex items-center gap-1 flex-wrap">
                        @foreach ($volunteer->categories as $cat)
                            <span class="text-xs px-2 py-0.5 rounded"
                                  style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}">
                                {{ $cat->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Lifetime hours</div>
                <div class="text-2xl font-semibold text-fct-navy">{{ number_format($totalHours, 1) }}</div>
            </div>
        </div>

        <div class="mt-6">
            <form method="POST" action="{{ route('admin.volunteers.destroy', $volunteer) }}"
                  onsubmit="return confirm('Delete this volunteer and all their signups? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">Delete volunteer</button>
            </form>
        </div>
    </div>

    <section class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-fct-navy">Upcoming signups</h2>
        </div>
        @if ($upcomingSignups->isEmpty())
            <div class="p-6 text-sm text-gray-500">No upcoming signups.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($upcomingSignups as $signup)
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div>
                            <a href="{{ route('admin.events.edit', $signup->position->event) }}" class="font-medium text-gray-900 hover:text-fct-navy">
                                {{ $signup->position->event->title }}
                            </a>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $signup->position->title }}
                                &middot; {{ $signup->position->event->starts_at->format('D, M j · g:i A') }}
                            </div>
                        </div>
                        @if ($signup->status === 'waitlisted')
                            <span class="text-xs px-2 py-1 rounded bg-yellow-50 text-yellow-800 font-medium">Waitlist</span>
                        @elseif ($signup->status === 'cancelled')
                            <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">Cancelled</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded bg-green-50 text-green-700 font-medium">Confirmed</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($pastSignups->isNotEmpty())
        <section class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-fct-navy">History</h2>
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach ($pastSignups as $signup)
                    <li class="p-4 flex items-center justify-between gap-4 text-sm">
                        <div>
                            <div class="font-medium text-gray-900">{{ $signup->position->event->title }}</div>
                            <div class="text-gray-600">
                                {{ $signup->position->title }}
                                &middot; {{ $signup->position->event->starts_at->format('M j, Y') }}
                                @if ($signup->hours_worked) &middot; {{ $signup->hours_worked }} hrs @endif
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_', ' ', $signup->status)) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layouts.admin>
