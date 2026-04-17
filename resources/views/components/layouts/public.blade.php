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
</head>
<body class="font-sans antialiased bg-fct-cream text-gray-900 min-h-screen flex flex-col">
    <header class="bg-fct-navy text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('calendar') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.png') }}"
                     alt="Florida Chautauqua Theater &amp; Institute"
                     class="h-12 sm:h-14 w-auto">
                <span class="hidden md:inline text-sm text-fct-cyan-light tracking-widest uppercase border-l border-fct-navy-light pl-3">
                    Volunteer Portal
                </span>
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="text-white hover:text-fct-cyan transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-fct-cyan-light hover:text-fct-cyan transition">Admin Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="bg-fct-navy-dark text-fct-cyan-light mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-center">
            &copy; {{ date('Y') }} The Florida Chautauqua Theater &amp; Institute
        </div>
    </footer>
</body>
</html>
