<x-mail::message>
# Hi {{ explode(' ', $user->name)[0] }},

@if ($signups->isEmpty())
Thanks for signing up as a volunteer! Your profile is saved — we'll email you when matching opportunities come up on the calendar.

<x-mail::button :url="route('calendar')" color="primary">
View the calendar
</x-mail::button>
@else
Thanks for volunteering. You're confirmed for the following:

@foreach ($signups as $signup)
@php $event = $signup->position->event; @endphp

## {{ $event->starts_at->format('F j, Y') }}

**{{ $event->title }}**

<x-mail::panel>
**Role:** {{ $signup->position->title }}

**Showtime:** {{ $event->starts_at->format('l, g:i A') }}

**Call time:** {{ $signup->position->starts_at->format('g:i A') }}

@if ($event->location)
**Where:** {{ $event->location }}
@endif

**Status:** {{ ucfirst($signup->status) }}
</x-mail::panel>

@endforeach

We'll send a reminder before each event. If anything changes, just reply to this email.

<x-mail::button :url="route('volunteer.dashboard')" color="primary">
View my signups
</x-mail::button>
@endif

Thanks,
Florida Chautauqua Theater
</x-mail::message>
