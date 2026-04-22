<x-mail::message>
# Hi {{ explode(' ', $admin->name)[0] }},

@if ($pending->count() === 1)
**1 volunteer** has signed up and is waiting for approval before they can browse and pick shifts.
@else
**{{ $pending->count() }} volunteers** have signed up and are waiting for approval before they can browse and pick shifts.
@endif

@foreach ($pending as $volunteer)
<x-mail::panel>
**{{ $volunteer->name }}** &mdash; {{ $volunteer->email }}

*Signed up {{ $volunteer->created_at->timezone(config('app.timezone'))->format('D, M j · g:i A') }}*
</x-mail::panel>

@endforeach

<x-mail::button :url="$reviewUrl" color="primary">
Review pending volunteers
</x-mail::button>

Thanks,
Florida Chautauqua Theater
</x-mail::message>
