<x-layouts.admin :title="'Volunteers · Admin'">
    <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <h1 class="text-2xl font-bold text-fct-navy">Volunteers</h1>
        <a href="{{ route('admin.volunteers.create') }}"
           class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            + Add volunteer
        </a>
    </div>

    <form method="GET" class="mb-4">
        <div class="relative max-w-md">
            <input type="search" name="q" value="{{ $q }}"
                   placeholder="Search by name, email, or phone…"
                   class="block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm text-sm pr-20">
            <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 text-sm bg-fct-navy text-white rounded-md hover:bg-fct-navy-light">Search</button>
        </div>
    </form>

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
                        <div class="text-sm text-right shrink-0 text-gray-600">
                            @if ($volunteer->upcoming_signups_count > 0)
                                {{ $volunteer->upcoming_signups_count }} upcoming
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
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
