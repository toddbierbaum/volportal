<x-layouts.admin :title="'Volunteers · Admin'">
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Volunteers</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Hours reporting and contact info.</p>
        </div>
        <a href="{{ route('admin.volunteers.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add volunteer
        </a>
    </div>

    <form method="GET" class="mb-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Search</label>
                <input type="search" name="q" value="{{ $q }}"
                       placeholder="Name, email, or phone…"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Hours from</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Through</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md text-sm">
            </div>
        </div>
        <div class="flex items-center justify-between flex-wrap gap-2">
            <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Leave dates blank for lifetime hours. Hours only count signups marked <strong>Attended</strong>.</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.volunteers.index') }}" class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100 underline">Reset</a>
                <a href="{{ route('admin.volunteers.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download CSV
                </a>
                <button type="submit" class="px-3 py-1.5 text-sm bg-fct-navy text-white rounded-md hover:bg-fct-navy-light">Apply</button>
            </div>
        </div>
    </form>

    <div class="mb-6 bg-gradient-to-r from-fct-cyan/10 to-fct-cyan/5 border border-fct-cyan/30 rounded-lg p-5 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <div class="text-xs text-fct-navy dark:text-fct-cyan uppercase tracking-wider font-semibold">
                Total hours {{ $from || $to ? '(filtered)' : '(lifetime)' }}
            </div>
            <div class="text-3xl font-semibold text-fct-navy dark:text-fct-cyan mt-1">{{ number_format((float) $totalHours, 2) }}</div>
        </div>
        @if ($from || $to)
            <div class="text-xs text-fct-navy dark:text-fct-cyan/70">
                {{ $from ?: 'earliest' }} → {{ $to ?: 'today' }}
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        @if ($volunteers->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">
                No volunteers {{ $q !== '' ? 'match that search.' : 'yet.' }}
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach ($volunteers as $volunteer)
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 dark:bg-gray-800/50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy dark:text-fct-cyan flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($volunteer->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.volunteers.show', $volunteer) }}"
                                   class="font-medium text-gray-900 dark:text-gray-100 hover:text-fct-navy dark:text-fct-cyan">{{ $volunteer->name }}</a>
                                <div class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5 truncate">
                                    {{ $volunteer->email }}
                                    @if ($volunteer->phone) &middot; {{ $volunteer->phone }} @endif
                                </div>
                                @if ($volunteer->categories->isNotEmpty())
                                    <div class="mt-1.5 flex items-center gap-1 flex-wrap">
                                        @foreach ($volunteer->categories as $cat)
                                            <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                                                  style="background-color: {{ $cat->color }}1A; color: {{ $cat->color }}">
                                                {{ $cat->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="text-sm text-right shrink-0">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ number_format((float) ($volunteer->hours_in_range ?? 0), 2) }}
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 dark:text-gray-500">hrs</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $volunteer->attended_count ?? 0 }} attended
                                @if ($volunteer->upcoming_signups_count > 0)
                                    &middot; {{ $volunteer->upcoming_signups_count }} upcoming
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
                {{ $volunteers->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin>
