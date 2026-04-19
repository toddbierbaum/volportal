<x-layouts.admin :title="$admin->name . ' · Admin'">
    <a href="{{ route('admin.admins.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Admins
    </a>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 max-w-2xl">
        <div class="flex items-center gap-4 mb-6">
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
                <div class="text-xs text-gray-400 mt-1">Joined {{ $admin->created_at->format('M j, Y') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.admins.update', $admin) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $admin->phone) }}"
                       placeholder="(850) 555-1234"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                    Save changes
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 max-w-2xl">
        <h2 class="text-base font-semibold text-gray-900 mb-1">Password</h2>
        @if ($admin->id === auth()->id())
            <p class="text-sm text-gray-500 mb-3">Change your own password — requires your current one.</p>
            <a href="{{ route('profile') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Change my password
            </a>
        @else
            <p class="text-sm text-gray-500 mb-3">Generate a new temporary password and share it with {{ $admin->name }} securely. Have them change it from their Profile page after they log in.</p>
            <form method="POST" action="{{ route('admin.admins.reset-password', $admin) }}"
                  onsubmit="return confirm('Reset this admin\'s password? A new temporary password will be shown to you.');"
                  class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700">
                    Reset password
                </button>
            </form>
        @endif
    </div>

    @if ($admin->id !== auth()->id())
        <div class="max-w-2xl">
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
