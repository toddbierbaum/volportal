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

    <div class="sm:col-span-2 pt-2 border-t border-gray-200 dark:border-gray-700">
        <label class="flex items-start gap-2 text-sm cursor-pointer">
            <input type="hidden" name="requires_background_check" value="0">
            <input type="checkbox" name="requires_background_check" value="1"
                   @checked(old('requires_background_check', (bool) ($template->requires_background_check ?? false)))
                   class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-fct-navy dark:text-fct-cyan focus:ring-fct-cyan">
            <span>
                <span class="text-gray-700 dark:text-gray-300 font-medium">Requires background check</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Volunteers who match any position on events from this template will be prompted to acknowledge a background check and placed in pending review until an admin verifies.</span>
            </span>
        </label>
    </div>
</div>
