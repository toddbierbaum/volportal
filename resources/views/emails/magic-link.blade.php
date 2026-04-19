<x-mail::message>
# Hi {{ explode(' ', $user->name)[0] }},

You asked for a login link for the Florida Chautauqua Theater volunteer portal. Click the button below to log in — it's good for 30 minutes.

<x-mail::button :url="$loginUrl" color="primary">
Log in
</x-mail::button>

If you didn't request this, you can safely ignore the email — no action was taken on your account.

Thanks,
Florida Chautauqua Theater
</x-mail::message>
