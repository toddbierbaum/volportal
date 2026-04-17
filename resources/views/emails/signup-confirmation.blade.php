<x-mail::message>
# Thanks for volunteering, {{ explode(' ', $user->name)[0] }}!

@if ($signups->isEmpty())
Your volunteer profile is saved. We'll email you when matching opportunities come up.
@else
You signed up for the following:

<x-mail::table>
| Event | Position | When | Status |
|:------|:---------|:-----|:-------|
@foreach ($signups as $signup)
| {{ $signup->position->event->title }} | {{ $signup->position->title }} | {{ $signup->position->event->starts_at->format('D, M j · g:i A') }} | {{ ucfirst($signup->status) }} |
@endforeach
</x-mail::table>

We'll send reminder emails before each event.

<x-mail::button :url="route('calendar')">
View the calendar
</x-mail::button>
@endif

If anything changes or you need to cancel, just reply to this email.

Thanks,<br>
Florida Chautauqua Theater
</x-mail::message>
