<x-layouts.admin :title="$admin->name . ' · Admin'">
    <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Admins
    </a>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 max-w-2xl">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-full bg-fct-cyan/15 text-fct-navy flex items-center justify-center font-semibold text-xl">
                {{ strtoupper(substr($admin->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $admin->name }}</h1>
                    @if ($admin->id === auth()->id())
                        <span class="text-xs px-2 py-0.5 rounded-full bg-fct-cyan/15 text-fct-navy font-medium">You</span>
                    @endif
                </div>
                <div class="mt-1 text-sm text-gray-500 space-y-0.5">
                    <div>{{ $admin->email }}</div>
                    <div class="text-xs text-gray-400">Joined {{ $admin->created_at->format('M j, Y') }}</div>
                </div>
            </div>
        </div>

        @if ($admin->id === auth()->id())
            <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
                This is your account — manage it from the <a href="{{ route('profile') }}" class="text-fct-navy underline hover:text-fct-navy-light">Profile page</a>.
            </div>
        @endif
    </div>

    @if ($admin->id !== auth()->id())
        <div class="flex items-center gap-3 flex-wrap">
            <form method="POST" action="{{ route('admin.admins.reset-password', $admin) }}"
                  onsubmit="return confirm('Reset this admin\'s password? A new temporary password will be shown to you — share it securely.');"
                  class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                    Reset password
                </button>
            </form>

            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                  onsubmit="return confirm('Delete this admin account? This cannot be undone.');"
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-md border border-red-200 text-red-700 bg-white hover:bg-red-50">
                    Delete admin
                </button>
            </form>
        </div>
    @endif
</x-layouts.admin>
