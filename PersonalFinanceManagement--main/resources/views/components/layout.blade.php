<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo {
            margin-right: auto;
            margin-left: 50px;
        }

        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        footer {
            text-align: center;
            padding: 1rem;
            background-color: #f8f9fa;
        }
        nav {
            margin-right: 50px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <x-application-logo class="w-32 h-24 text-gray-500 fill-current" /> <!-- Adjust width and height here -->
        </div>
        <nav>
            <div class="flex space-x-4 margin-right: 50px">
                @auth
                    @if (auth()->user()->role == 'admin')
                    <a href="{{ route('admin.home') }}"
                        class="px-4 py-2 text-gray-700 transition rounded-md hover:text-black hover:bg-gray-500">
                        Home
                    </a>
                    @elseif (auth()->user()->role == 'user')
                    <a href="{{ url('/home') }}"
                        class="px-4 py-2 text-gray-700 transition rounded-md hover:text-black hover:bg-gray-500">
                        Home
                    </a>
                    @endif

                @endauth

                @guest
                    <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 transition rounded-md hover:bg-gray-200">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="px-4 py-2 text-white transition bg-black rounded-md hover:text-white hover:bg-gray-800">
                            Register
                        </a>
                    @endif
                @endguest
            </div>
        </nav>
    </header>

    <main class="flex items-center justify-center p-2">
        {{ $slot }}
    </main>

    <footer class="p-2">
        <hr class="my-4 border-gray-300">

        <p>&copy; 2023 - {{ date('Y') }}. All rights reserved.</p>
        <p><strong>TruWealth</strong></p>
        <p>Designed and developed by<strong> Abdul Aziz Abdul Hamid Pathan </strong></span>

        <div class="flex items-center justify-center p-2">
            <a href="tel:+91 7263897813">
                <img src="{{ asset('logo/whatsapp.png') }}" class="cursor-pointer h-9 w-9" alt="Phone Logo">
            </a>
            <a href="https://www.instagram.com/a.azizpathan/" target="_blank" class="mx-2">
                <img src="{{ asset('logo/instagram.jpg') }}" class="w-8 h-8 cursor-pointer" alt="Instagram Logo">
            </a>
            <a href="mailto:abdulazizpathan41@gmail.com?subject=Web Application Development" target="_blank">
                <img src="{{ asset('logo/email.webp') }}" class="cursor-pointer h-9 w-9" alt="Email Logo">
            </a>
        </div>
    </footer>
</body>

</html>
