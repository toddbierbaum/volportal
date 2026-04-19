<x-layouts.admin :title="'Admins · Admin'">
    <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Admins</h1>
            <p class="mt-1 text-sm text-gray-500">People who can publish events, manage volunteers, and access this dashboard.</p>
        </div>
        <a href="{{ route('admin.admins.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add admin
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
        @if ($admins->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">No admins yet.</div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($admins as $admin)
                    <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.admins.show', $admin) }}"
                                       class="font-medium text-gray-900 hover:text-fct-navy">{{ $admin->name }}</a>
                                    @if ($admin->id === auth()->id())
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-fct-cyan/15 text-fct-navy font-medium">You</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500 mt-0.5">{{ $admin->email }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">Joined {{ $admin->created_at->format('M j, Y') }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.admins.show', $admin) }}" class="text-sm text-fct-navy hover:underline shrink-0">Manage</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
