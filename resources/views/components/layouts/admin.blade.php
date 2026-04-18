<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin · ' . config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 min-h-screen flex flex-col">
    <header class="bg-fct-navy text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="/images/logo-white.png?v={{ config('app.version') }}"
                     alt="Florida Chautauqua Theater &amp; Institute"
                     class="h-10 w-auto">
                <span class="hidden md:inline text-xs text-fct-cyan-light tracking-widest uppercase border-l border-fct-navy-light pl-3">
                    Admin
                </span>
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('calendar') }}" class="text-fct-cyan-light hover:text-white transition">Public site</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-fct-cyan-light hover:text-white transition">Log out</button>
                </form>
            </nav>
        </div>
        <nav class="bg-fct-navy-light border-t border-fct-navy-dark/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-1 overflow-x-auto text-sm">
                @php
                    $tabs = [
                        ['admin.dashboard', 'Dashboard'],
                        ['admin.events.index', 'Events'],
                        ['admin.volunteers.index', 'Volunteers'],
                        ['admin.categories', 'Categories'],
                        ['admin.position-templates', 'Templates'],
                        ['admin.event-types', 'Event types'],
                        ['admin.notification-schedules', 'Reminders'],
                    ];
                @endphp
                @foreach ($tabs as [$routeName, $label])
                    @php $active = request()->routeIs($routeName) || request()->routeIs(str_replace('.index','.*',$routeName)); @endphp
                    <a href="{{ route($routeName) }}"
                       class="px-3 py-2.5 border-b-2 whitespace-nowrap {{ $active ? 'border-fct-cyan text-white font-medium' : 'border-transparent text-fct-cyan-light hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>
    </header>

    @if (session('status'))
        <div class="bg-green-50 border-b border-green-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-sm text-green-900">
                {{ session('status') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </div>
    </main>

    <footer class="text-xs text-gray-500 text-center py-4">
        {{ config('app.version') }}
    </footer>
</body>
</html>
