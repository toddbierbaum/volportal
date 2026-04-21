<x-mail::message>
# Hi {{ explode(' ', $user->name)[0] }},

Here are open volunteer positions matching your interests. Click any to view and sign up.

@foreach ($positions as $position)
@php $event = $position->event; @endphp

<x-mail::panel>
**{{ $event->title }}** &mdash; {{ $event->starts_at->format('D, M j · g:i A') }}

**Role:** {{ $position->title }}
@if ($event->location)

**Where:** {{ $event->location }}
@endif
@if ($position->isFull())

*Currently full — join the waitlist.*
@endif
</x-mail::panel>

@endforeach

<x-mail::button :url="$dashboardUrl" color="primary">
Browse and sign up
</x-mail::button>

Thanks,
Florida Chautauqua Theater

<x-slot:subcopy>
You're getting this monthly alert because you opted in when you signed up to volunteer. [Manage your email preferences]({{ $preferencesUrl }}) to stop receiving these.
</x-slot:subcopy>
</x-mail::message>
