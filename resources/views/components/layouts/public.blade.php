<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php $gaId = \App\Models\Setting::get('google_analytics_code', ''); @endphp
    @if ($gaId && preg_match('/^G-[A-Z0-9]+$/', $gaId))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif
</head>
<body class="font-sans antialiased bg-fct-cream dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <header class="bg-fct-navy text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('calendar') }}" class="flex items-center gap-3 group">
                <img src="/images/logo-white.png?v={{ config('app.version') }}"
                     alt="Florida Chautauqua Theater &amp; Institute"
                     class="h-12 sm:h-14 w-auto">
                <span class="hidden md:inline text-sm text-fct-cyan-light tracking-widest uppercase border-l border-fct-navy-light pl-3">
                    Volunteer Portal
                </span>
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('dashboard') }}"
                           class="text-white hover:text-fct-cyan transition">Dashboard</a>
                    @else
                        <a href="{{ route('volunteer.dashboard') }}"
                           class="text-white hover:text-fct-cyan transition">My signups</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-fct-cyan-light hover:text-fct-cyan transition">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login-link') }}"
                       class="text-white hover:text-fct-cyan transition">Volunteer Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <livewire:welcome-banner />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-fct-navy-dark text-fct-cyan-light mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-center">
            <div>&copy; {{ date('Y') }} The Florida Chautauqua Theater &amp; Institute</div>
            <div class="mt-2">
                <a href="{{ route('privacy') }}" class="text-fct-cyan-light/80 hover:text-fct-cyan underline-offset-2 hover:underline">Privacy Policy</a>
            </div>
            <div class="mt-1 text-xs text-fct-cyan-light/60">{{ config('app.version') }}</div>
        </div>
    </footer>
</body>
</html>
