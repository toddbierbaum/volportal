<x-layouts.admin :title="'Volunteers · Admin'">
    <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <h1 class="text-2xl font-bold text-fct-navy">Volunteers</h1>
        <a href="{{ route('admin.volunteers.create') }}"
           class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            + Add volunteer
        </a>
    </div>

    <form method="GET" class="mb-4 bg-white border border-gray-200 rounded-lg shadow-sm p-4 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Search</label>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Name, email, or phone…"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Hours from</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Through</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm">
            </div>
        </div>
        <div class="flex items-center justify-between flex-wrap gap-2">
            <p class="text-xs text-gray-500">Leave dates blank for lifetime hours. Hours only count signups marked <strong>Attended</strong>.</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.volunteers.index') }}" class="text-xs text-gray-500 hover:text-gray-900 underline">Reset</a>
                <a href="{{ route('admin.volunteers.export', request()->query()) }}"
                   class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">
                    Download CSV
                </a>
                <button type="submit" class="px-3 py-1.5 text-sm bg-fct-navy text-white rounded hover:bg-fct-navy-light">Apply</button>
            </div>
        </div>
    </form>

    <div class="mb-4 bg-fct-cyan-light/30 border border-fct-cyan-light rounded-lg p-4 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <div class="text-xs text-fct-navy uppercase tracking-wide font-semibold">Total hours {{ $from || $to ? '(filtered)' : '(lifetime)' }}</div>
            <div class="text-3xl font-bold text-fct-navy mt-0.5">{{ number_format((float) $totalHours, 2) }}</div>
        </div>
        @if ($from || $to)
            <div class="text-xs text-fct-navy/70">
                {{ $from ?: 'earliest' }} &rarr; {{ $to ?: 'today' }}
            </div>
        @endif
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        @if ($volunteers->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">
                No volunteers {{ $q !== '' ? 'match that search.' : 'yet.' }}
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($volunteers as $volunteer)
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('admin.volunteers.show', $volunteer) }}"
                               class="font-medium text-gray-900 hover:text-fct-navy">{{ $volunteer->name }}</a>
                            <div class="text-sm text-gray-600 mt-0.5">
                                {{ $volunteer->email }}
                                @if ($volunteer->phone) &middot; {{ $volunteer->phone }} @endif
                            </div>
                            @if ($volunteer->categories->isNotEmpty())
                                <div class="mt-1 flex items-center gap-1 flex-wrap">
                                    @foreach ($volunteer->categories as $cat)
                                        <span class="text-xs px-2 py-0.5 rounded"
                                              style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}">
                                            {{ $cat->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-sm text-right shrink-0 min-w-0">
                            <div class="font-semibold text-fct-navy">
                                {{ number_format((float) ($volunteer->hours_in_range ?? 0), 2) }} hrs
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $volunteer->attended_count ?? 0 }} attended
                                @if ($volunteer->upcoming_signups_count > 0)
                                    &middot; {{ $volunteer->upcoming_signups_count }} upcoming
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="p-4 border-t border-gray-200">
                {{ $volunteers->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
