<x-mail::message>
# Reminder — you're volunteering soon

Hi {{ explode(' ', $signup->user->name)[0] }},

This is your **{{ $schedule->label }}** reminder for:

<x-mail::panel>
**{{ $signup->position->event->title }}**

{{ $signup->position->title }}
{{ $signup->position->event->starts_at->format('l, F j · g:i A') }}
@if ($signup->position->event->location)
at {{ $signup->position->event->location }}
@endif
</x-mail::panel>

@if ($signup->status === 'waitlisted')
You're currently on the **waitlist** — we'll email you again if a spot opens up.
@endif

<x-mail::button :url="route('volunteer.dashboard')">
View my signups
</x-mail::button>

If you can't make it, please reply to this email so we can fill your spot.

Thanks,<br>
Florida Chautauqua Theater
</x-mail::message>
