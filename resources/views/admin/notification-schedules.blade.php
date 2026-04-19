<x-layouts.admin :title="'Reminders · Admin'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Reminder schedules</h1>
        <p class="mt-1 text-sm text-gray-500">Global defaults. Event templates and individual events can override these.</p>
    </div>
    <livewire:admin.notification-schedule-manager />
</x-layouts.admin>
