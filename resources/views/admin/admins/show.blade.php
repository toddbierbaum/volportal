<x-layouts.admin :title="$admin->name . ' · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.admins.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Admins</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">{{ $admin->name }}</h1>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6 max-w-2xl">
        <div class="text-sm text-gray-700 space-y-0.5">
            <div><span class="text-gray-500">Email:</span> {{ $admin->email }}</div>
            <div><span class="text-gray-500">Joined:</span> {{ $admin->created_at->format('M j, Y') }}</div>
            @if ($admin->id === auth()->id())
                <div class="mt-2 text-sm text-fct-navy">This is your account — manage it from the <a href="{{ route('profile') }}" class="underline">Profile page</a>.</div>
            @endif
        </div>
    </div>

    @if ($admin->id !== auth()->id())
        <div class="flex items-center gap-3 flex-wrap">
            <form method="POST" action="{{ route('admin.admins.reset-password', $admin) }}"
                  onsubmit="return confirm('Reset this admin\'s password? A new temporary password will be shown to you — share it securely.');"
                  class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50">
                    Reset password
                </button>
            </form>

            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                  onsubmit="return confirm('Delete this admin account? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm rounded border border-red-300 text-red-700 bg-white hover:bg-red-50">
                    Delete admin
                </button>
            </form>
        </div>
    @endif
</x-layouts.admin>
