<x-layouts.admin :title="'Add admin · Admin'">
    <div class="mb-6">
        <a href="{{ route('admin.admins.index') }}" class="text-sm text-gray-600 hover:text-fct-navy">&larr; Admins</a>
        <h1 class="mt-1 text-2xl font-bold text-fct-navy">Add admin</h1>
        <p class="text-sm text-gray-600 mt-1">Pick an initial password; share it with the new admin securely and have them change it on their Profile page.</p>
    </div>

    <form method="POST" action="{{ route('admin.admins.store') }}"
          class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 max-w-2xl">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="password" class="block text-sm font-medium text-gray-700">Initial password</label>
                <input type="text" id="password" name="password" value="{{ old('password') }}" required minlength="8"
                       class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm font-mono">
                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters. Shown here so you can copy it before saving.</p>
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.admins.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-fct-navy text-white rounded-md text-sm font-medium hover:bg-fct-navy-light">
                Add admin
            </button>
        </div>
    </form>
</x-layouts.admin>
