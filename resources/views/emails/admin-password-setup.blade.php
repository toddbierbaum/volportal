<x-mail::message>
# Hi {{ explode(' ', $admin->name)[0] }},

Another admin has reset your password for the Florida Chautauqua Theater volunteer portal. Click the button below to set a new one — this link is good for 24 hours.

<x-mail::button :url="$setupUrl" color="primary">
Set my password
</x-mail::button>

If you weren't expecting this, contact another admin immediately — no one can log in to your account until you click the link and set a password.

Florida Chautauqua Theater
</x-mail::message>
