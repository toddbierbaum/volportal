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

Call time: {{ $signup->position->starts_at->format('g:i A') }} ({{ $signup->position->starts_at->diffForHumans($signup->position->event->starts_at, ['parts' => 2, 'short' => true]) }} before showtime)
</x-mail::panel>

@if ($signup->position->description)
### What you'll be doing

{{ $signup->position->description }}
@endif

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
