<x-mail::message>
# Hi {{ explode(' ', $admin->name)[0] }},

Someone (hopefully you) just tried to log in or sign up as a volunteer using this email address. This address belongs to an **administrator** account — admin accounts sign in with a password, not a volunteer login link.

Use the button below to log in to your account.

<x-mail::button :url="$loginUrl" color="primary">
Log in to your account
</x-mail::button>

If this wasn't you, you can safely ignore this email — no action was taken on your account.

Thanks,
Florida Chautauqua Theater
</x-mail::message>
