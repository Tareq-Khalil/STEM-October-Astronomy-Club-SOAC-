<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    
    

                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
                        Sign in to your account
                    </h1>
                    <form method="POST" action="{{ route('login') }}" class="space-y-4 md:space-y-6">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
                            <input type="email" name="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="name@company.com" required autofocus autocomplete="username" value="{{ old('email') }}">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" required autocomplete="current-password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="remember_me" name="remember" type="checkbox" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="remember_me" class="text-gray-500">Remember me</label>
                                </div>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-medium  text-black transition  rounded-md hover:text-black600 hover:underline">Forgot password?</a>
                            @endif
                        </div>

                        <button type="submit" class="w-full text-white transition bg-black rounded-md hover:text-white hover:bg-gray-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Sign in</button>

                        <p class="text-sm font-light text-gray-500">
                            Don’t have an account yet? <a href="/register" class="font-medium text-black transition  rounded-md hover:text-black600 hover:underline">Sign up</a>
                        </p>
                    </form>
            
</x-guest-layout>