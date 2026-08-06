<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            @if (session('success'))
                <div id="toast-success" class="fixed top-24 left-1/2 -translate-x-1/2 bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-lg border border-emerald-500/30 z-50 transition-opacity duration-1000 whitespace-nowrap">
                    <span class="text-sm font-semibold">
                        {{ session('success') }}
                    </span>
                </div>
            @endif

            @if (session('error'))
                <div id="toast-error" class="fixed top-24 left-1/2 -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-xl shadow-lg border border-red-500/30 z-50 transition-opacity duration-1000 whitespace-nowrap">
                    <span class="text-sm font-semibold">
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            @if (session('success') || session('error'))
                <script>
                    // Looking for all toasts (both success and error)
                    document.querySelectorAll('[id^="toast-"]').forEach(toast => {
                        setTimeout(() => {
                            toast.classList.add('opacity-0'); // Dissolving it smoothly in 1 second
                            setTimeout(() => toast.remove(), 1000); // Completely removing it from the DOM
                        }, 5000);
                    });
                </script>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
