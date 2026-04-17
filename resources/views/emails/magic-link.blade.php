<x-mail::message>
# Welcome back, {{ explode(' ', $user->name)[0] }}

Click the button below to log in. The link is good for 30 minutes.

<x-mail::button :url="$loginUrl">
Log in
</x-mail::button>

If you didn't request this, you can safely ignore the email.

Thanks,<br>
Florida Chautauqua Theater
</x-mail::message>
