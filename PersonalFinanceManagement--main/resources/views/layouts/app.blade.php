<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="//unpkg.com/alpinejs" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')
            <div id="accountForm" role="dialog" aria-describedby="radix-:r3:" aria-labelledby="radix-:r2:" data-state="closed" data-vaul-drawer-direction="bottom" data-vaul-drawer="" data-vaul-delayed-snap-points="false" data-vaul-snap-points="false" data-vaul-custom-container="false" data-vaul-animate="true" class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-[10px] border bg-white transform transition-transform duration-300 ease-in-out translate-y-full" tabindex="-1" style="pointer-events: auto;">
    <div class="mx-auto mt-4 h-2 w-[100px] rounded-full bg-muted"></div>
    <div class="grid gap-1.5 p-4 text-center sm:text-left">
        <h2 id="radix-:r2:" class="text-lg font-semibold leading-none tracking-tight">Create New Account</h2>
    </div>
    <div class="px-4 pb-4">
        <livewire:account-component />
    </div>
</div>
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
