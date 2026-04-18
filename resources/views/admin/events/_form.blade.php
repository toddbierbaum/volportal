@php
    $starts = old('starts_at', $event->starts_at?->format('Y-m-d\TH:i'));
    $ends = old('ends_at', $event->ends_at?->format('Y-m-d\TH:i'));
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}" required
               class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    @if ($event->template)
        <div>
            <label class="block text-sm font-medium text-gray-700">Template</label>
            <div class="mt-1 flex items-center gap-2 text-sm">
                <span class="inline-block w-3 h-3 rounded" style="background-color: {{ $event->template->color }}"></span>
                <span class="font-medium text-gray-900">{{ $event->template->name }}</span>
            </div>
        </div>
    @endif

    <div>
        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
        <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}"
               class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
    </div>

    <div>
        <label for="starts_at" class="block text-sm font-medium text-gray-700">Starts at</label>
        <input type="datetime-local" name="starts_at" id="starts_at" value="{{ $starts }}" required
               class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
        @error('starts_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="ends_at" class="block text-sm font-medium text-gray-700">Ends at</label>
        <input type="datetime-local" name="ends_at" id="ends_at" value="{{ $ends }}" required
               class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">
        @error('ends_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="4"
                  class="mt-1 block w-full border-gray-300 focus:border-fct-cyan focus:ring-fct-cyan rounded-md shadow-sm">{{ old('description', $event->description) }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $event->is_published))
                   class="rounded border-gray-300 text-fct-navy focus:ring-fct-cyan">
            <span class="ml-2 text-sm text-gray-700">Published (visible to volunteers)</span>
        </label>
    </div>
</div>
