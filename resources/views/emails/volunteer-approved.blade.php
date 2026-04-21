<x-mail::message>
# Welcome aboard, {{ explode(' ', $user->name)[0] }}!

Your application has been approved. You can now browse open volunteer opportunities and pick the shifts that work for you.

<x-mail::button :url="$loginUrl" color="primary">
Pick your shifts
</x-mail::button>

This link is good for 7 days. If it expires, you can always request a new login link from the [login page]({{ route('login-link') }}).

Thanks for volunteering with us,
Florida Chautauqua Theater

<x-slot:subcopy>
You're getting this because you applied to volunteer at {{ config('app.url') }}. [Manage your email preferences]({{ $preferencesUrl }}).
</x-slot:subcopy>
</x-mail::message>
