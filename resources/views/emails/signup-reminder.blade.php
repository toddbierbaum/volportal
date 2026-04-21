<x-mail::message>
# Hi {{ explode(' ', $signup->user->name)[0] }},

This is your **{{ $schedule->label }}** reminder for the upcoming show. Here are the details — see you there.

@php $event = $signup->position->event; @endphp

## {{ $event->starts_at->format('F j, Y') }}

**{{ $event->title }}**

<x-mail::panel>
**Role:** {{ $signup->position->title }}

**Showtime:** {{ $event->starts_at->format('l, g:i A') }}

**Call time:** {{ $signup->position->starts_at->format('g:i A') }} ({{ $signup->position->starts_at->diffForHumans($event->starts_at, ['parts' => 2, 'short' => true]) }} before showtime)

@if ($event->location)
**Where:** {{ $event->location }}
@endif

@if ($signup->status === 'waitlisted')
**Status:** Waitlisted — we'll email again if a spot opens up.
@endif
</x-mail::panel>

@if ($signup->position->description)
### What you'll be doing

{{ $signup->position->description }}
@endif

<x-mail::button :url="route('volunteer.dashboard')" color="primary">
View my signups
</x-mail::button>

If you can't make it, please reply to this email so we can fill your spot.

Thanks,
Florida Chautauqua Theater

<x-slot:subcopy>
You're getting this reminder because you signed up for a shift at {{ config('app.url') }}. [Manage your email preferences]({{ $preferencesUrl }}).
</x-slot:subcopy>
</x-mail::message>
