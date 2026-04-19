<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin · ' . config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Apply theme before paint so there's no flash. --}}
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{
        sidebarOpen: false,
        dark: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }
      }"
      class="font-sans antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 min-h-screen">

    @php
        $tabs = [
            ['admin.dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['admin.events.index', 'Events', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['admin.event-templates.index', 'Event templates', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['admin.volunteers.index', 'Volunteers', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['admin.categories', 'Categories', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['admin.notification-schedules', 'Reminders', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['admin.admins.index', 'Admins', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
        ];
    @endphp

    {{-- Mobile header with hamburger --}}
    <header class="lg:hidden bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
        <div class="flex items-center justify-between px-4 py-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <img src="/images/logo-dark.png?v={{ config('app.version') }}"
                     alt="FCT"
                     class="h-8 w-auto dark:brightness-0 dark:invert">
                <span class="text-sm font-semibold text-fct-navy dark:text-fct-cyan">Admin</span>
            </a>
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity
         @click="sidebarOpen = false"
         class="lg:hidden fixed inset-0 bg-gray-900/40 z-30"
         style="display: none;"></div>

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col
                      transform transition-transform lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            {{-- Logo --}}
            <div class="h-16 flex items-center gap-3 px-5 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0">
                    <img src="/images/logo-dark.png?v={{ config('app.version') }}"
                         alt="Florida Chautauqua Theater &amp; Institute"
                         class="h-9 w-auto dark:brightness-0 dark:invert">
                    <span class="text-xs tracking-[0.2em] uppercase text-gray-500 dark:text-gray-400 truncate">Admin</span>
                </a>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">
                @foreach ($tabs as [$routeName, $label, $iconPath])
                    @php $active = request()->routeIs($routeName) || request()->routeIs(str_replace('.index','.*',$routeName)); @endphp
                    <a href="{{ route($routeName) }}"
                       class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm transition
                              {{ $active
                                    ? 'bg-fct-cyan/10 dark:bg-fct-cyan/20 text-fct-navy dark:text-fct-cyan font-semibold'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100' }}">
                        <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-fct-navy dark:text-fct-cyan' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $iconPath }}" />
                        </svg>
                        <span class="truncate">{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- User footer --}}
            <div class="border-t border-gray-200 dark:border-gray-700 p-3">
                <a href="{{ route('profile') }}"
                   class="flex items-center gap-3 px-2 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                   title="Edit your profile">
                    <div class="h-9 w-9 rounded-full bg-fct-cyan/20 text-fct-navy dark:text-fct-cyan flex items-center justify-center font-semibold text-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ auth()->user()?->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()?->email }}</div>
                    </div>
                </a>
                <div class="mt-2 flex items-center justify-between gap-1 text-xs">
                    <a href="{{ route('calendar') }}" class="text-gray-600 dark:text-gray-400 hover:text-fct-navy dark:hover:text-fct-cyan px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Public site</a>
                    <button type="button" @click="toggleTheme"
                            class="text-gray-600 dark:text-gray-400 hover:text-fct-navy dark:hover:text-fct-cyan p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                            :title="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                        <svg x-show="!dark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="dark" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-fct-navy dark:hover:text-fct-cyan px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">Log out</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            @if (session('status'))
                <div class="bg-emerald-50 dark:bg-emerald-900/30 border-b border-emerald-200 dark:border-emerald-800">
                    <div class="px-4 sm:px-6 lg:px-8 py-3 text-sm text-emerald-900 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <main class="flex-1">
                <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl">
                    {{ $slot }}
                </div>
            </main>

            <footer class="text-xs text-gray-400 dark:text-gray-600 text-center py-4 px-4">
                v{{ config('app.version') }}
            </footer>
        </div>
    </div>
</body>
</html>
