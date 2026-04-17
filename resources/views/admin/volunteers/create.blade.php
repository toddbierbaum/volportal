<x-layouts.admin :title="'Add volunteer · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.volunteers.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Volunteers</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">Add volunteer</h1>
    </div>

    <form method="POST" action="{{ route('admin.volunteers.store') }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 max-w-2xl">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Interest categories</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($categories as $cat)
                        <label class="inline-flex items-center text-sm">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                   @checked(in_array($cat->id, old('categories', [])))
                                   class="rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
                            <span class="ml-2">{{ $cat->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.volunteers.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Add volunteer
            </button>
        </div>
    </form>
</x-layouts.admin>
