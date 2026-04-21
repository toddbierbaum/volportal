<x-layouts.admin :title="'Reminders · Admin'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Reminders &amp; notifications</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Volunteer email and SMS notifications.</p>
    </div>

    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-2">Shift reminder schedules</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Global defaults. Event templates and individual events can override these.</p>
        <livewire:admin.notification-schedule-manager />
    </div>

    <div>
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-2">Scheduled broadcasts</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Recurring digest emails sent on a fixed cadence, not tied to a specific event.</p>
        <livewire:admin.scheduled-broadcasts-manager />
    </div>
</x-layouts.admin>
