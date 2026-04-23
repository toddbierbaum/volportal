<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <x-theme-init />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased bg-fct-cream dark:bg-gray-900">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="{{ route('calendar') }}">
                    <x-application-logo class="h-12 w-auto" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg border border-gray-200 dark:border-gray-700">
                {{ $slot }}
            </div>

            <div class="mt-6 text-xs text-gray-500 dark:text-gray-400">
                {{ config('app.version') }}
            </div>
        </div>
    </body>
</html>
