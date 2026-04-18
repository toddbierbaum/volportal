<x-layouts.admin :title="'Admins · Admin'">
    <div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
        <h1 class="text-2xl font-bold text-fct-navy">Admins</h1>
        <a href="{{ route('admin.admins.create') }}"
           class="inline-flex items-center px-4 py-2 bg-fct-navy rounded-md font-medium text-white text-sm hover:bg-fct-navy-light transition">
            + Add admin
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        @if ($admins->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">No admins yet.</div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($admins as $admin)
                    <li class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('admin.admins.show', $admin) }}"
                               class="font-medium text-gray-900 hover:text-fct-navy">{{ $admin->name }}</a>
                            <div class="text-sm text-gray-600 mt-0.5">{{ $admin->email }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Joined {{ $admin->created_at->format('M j, Y') }}
                                @if ($admin->id === auth()->id())
                                    &middot; <span class="text-fct-navy">That's you</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.admins.show', $admin) }}" class="text-sm text-fct-navy hover:underline">Manage</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.admin>
