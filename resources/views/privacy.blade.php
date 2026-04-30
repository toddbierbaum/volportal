<x-layouts.public :title="'Privacy Policy · ' . config('app.name')">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-fct-navy dark:text-fct-cyan tracking-tight">Privacy Policy</h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Last updated: {{ date('F j, Y') }}</p>

        <div class="prose prose-sm sm:prose-base dark:prose-invert max-w-none mt-6 text-gray-700 dark:text-gray-300">
            <h2>About this site</h2>
            <p>
                The Florida Chautauqua Theater &amp; Institute (&ldquo;FCT,&rdquo; &ldquo;we,&rdquo; or &ldquo;us&rdquo;)
                operates this volunteer portal to coordinate scheduling and communication with our volunteers.
                This policy explains what information we collect and how we use it.
            </p>

            <h2>Information we collect</h2>
            <ul>
                <li><strong>Account information</strong> &mdash; your name, email address, and (optional) phone number, provided when you sign up.</li>
                <li><strong>Volunteer preferences</strong> &mdash; the categories of work you're interested in and any background-check or eligibility information required for specific roles.</li>
                <li><strong>Activity</strong> &mdash; the shifts you sign up for, attend, and the volunteer hours we record for you.</li>
            </ul>

            <h2>How we use your information</h2>
            <ul>
                <li>To match you with upcoming volunteer opportunities.</li>
                <li>To send you reminders and coordination messages by email and, if you opt in, by text message.</li>
                <li>To track volunteer hours for our internal records and reporting.</li>
            </ul>

            <h2>SMS / text messaging &mdash; mobile information</h2>
            <p>
                If you opt in to receive text messages, we use your mobile phone number only to send you
                shift reminders, fill-in requests, and last-minute schedule changes related to your
                volunteer commitments with the Florida Chautauqua Theater.
            </p>
            <p>
                <strong>
                    No mobile information will be shared with third parties or affiliates for marketing
                    or promotional purposes. Information sharing to subcontractors in support of our
                    services (for example, our SMS delivery provider) is permitted. All other use case
                    categories exclude text messaging originator opt-in data and consent; this information
                    will not be shared with any third parties.
                </strong>
            </p>
            <p>
                Message frequency varies based on your volunteer schedule. Message and data rates may
                apply. Reply <strong>STOP</strong> to any text to unsubscribe at any time, or
                <strong>HELP</strong> for help. You can also turn text reminders on or off at any time
                from your volunteer dashboard.
            </p>

            <h2>Sharing</h2>
            <p>
                We do not sell or rent your personal information. We share information only with:
            </p>
            <ul>
                <li>Service providers we use to operate this site (for example, our email and SMS delivery providers), which act on our behalf.</li>
                <li>Authorities when required by law.</li>
            </ul>

            <h2>Data retention</h2>
            <p>
                We retain your volunteer record and hours for as long as you remain an active volunteer
                and for a reasonable period afterward for our recordkeeping. You may request deletion of
                your account at any time by contacting us.
            </p>

            <h2>Contact</h2>
            <p>
                Questions about this policy or your information can be directed to the Florida Chautauqua
                Theater &amp; Institute via the contact information on
                <a href="https://flchautauqua.org" class="text-fct-navy dark:text-fct-cyan hover:underline">flchautauqua.org</a>.
            </p>
        </div>
    </div>
</x-layouts.public>
