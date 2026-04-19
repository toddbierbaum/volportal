<x-layouts.admin :title="'Add volunteer · Admin'">
    <a href="{{ route('admin.volunteers.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-fct-navy mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Volunteers
    </a>

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Add volunteer</h1>

    <form method="POST" action="{{ route('admin.volunteers.store') }}"
          class="bg-white rounded-lg border border-gray-200 p-6 max-w-2xl">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Interest categories</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($categories as $cat)
                        <label class="inline-flex items-center text-sm px-3 py-2 rounded-md border border-gray-200 hover:bg-gray-50 cursor-pointer transition">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   @checked(in_array($cat->id, old('categories', [])))
                                   class="rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                            <span class="ml-2 flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full" style="background-color: {{ $cat->color ?? '#9CA3AF' }}"></span>
                                {{ $cat->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.volunteers.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Add volunteer
            </button>
        </div>
    </form>
</x-layouts.admin>
