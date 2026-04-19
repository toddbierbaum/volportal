<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $template->name) }}" required
               class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-xs">
        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
        <input type="color" id="color" name="color" value="{{ old('color', $template->color ?? '#4F46E5') }}"
               class="mt-1 block h-9 w-24 border-gray-300 dark:border-gray-600 rounded-md">
        @error('color') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div></div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (optional)</label>
        <textarea name="description" id="description" rows="2"
                  class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-xs">{{ old('description', $template->description) }}</textarea>
    </div>
</div>
