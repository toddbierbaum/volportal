<x-layouts.admin :title="'My profile · Admin'">
    <h1 class="text-2xl font-bold text-fct-navy dark:text-fct-cyan mb-6">My profile</h1>

    <div class="max-w-2xl space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xs p-6">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-xs p-6">
            <livewire:profile.update-password-form />
        </div>
    </div>
</x-layouts.admin>
