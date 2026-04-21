<x-layouts.admin :title="'Add volunteer · Admin'">
    <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-fct-navy dark:text-fct-cyan mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volunteers
    </a>

    <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Add volunteer</h1>

    <form method="POST" action="{{ route('admin.volunteers.store') }}"
          class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-2xl">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Interest categories</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($categories as $cat)
                        <label class="inline-flex items-center text-sm px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:bg-gray-800/50 cursor-pointer transition">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   @checked(in_array($cat->id, old('categories', [])))
                                   class="rounded-sm border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                            <span class="ml-2 flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $cat->color ?? '#9CA3AF' }}"></span>
                                {{ $cat->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Self-attestation capture. When ticked here, we stamp the
                 timestamp on create — same effect as if the volunteer had
                 completed the cert screens in the signup wizard. These are
                 immutable after save (audit trail for an automated BG-check
                 provider that needs proof of consent). --}}
            <div class="sm:col-span-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Volunteer self-attestations</div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 mb-3">
                    Tick these if the volunteer has affirmed either (e.g. on a paper form). They're locked once saved.
                </p>

                <label class="flex items-start gap-2 text-sm cursor-pointer mb-2">
                    <input type="hidden" name="age_certified" value="0">
                    <input type="checkbox" name="age_certified" value="1"
                           @checked(old('age_certified'))
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span class="text-gray-700 dark:text-gray-300">Volunteer has certified they are 18 or older</span>
                </label>

                <label class="flex items-start gap-2 text-sm cursor-pointer">
                    <input type="hidden" name="background_check_acknowledged" value="0">
                    <input type="checkbox" name="background_check_acknowledged" value="1"
                           @checked(old('background_check_acknowledged'))
                           class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
                    <span class="text-gray-700 dark:text-gray-300">Volunteer has consented to a background check</span>
                </label>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-end gap-3 flex-wrap">
            <a href="{{ route('admin.volunteers.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100">Cancel</a>
            <button type="submit" name="action" value="pending"
                    class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300 font-medium">
                Save as pending
            </button>
            <button type="submit" name="action" value="approve"
                    class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Save & send approval email
            </button>
        </div>
    </form>
</x-layouts.admin>
