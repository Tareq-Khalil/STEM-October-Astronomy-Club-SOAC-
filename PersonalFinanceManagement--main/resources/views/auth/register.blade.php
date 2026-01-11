<x-guest-layout>


  
<div class="p-6 space-y-4 md:space-y-6 sm:p-3">
        <!-- Heading -->
        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl">
          Create an account
        </h1>

        <!-- Form -->
        <form method="POST" action="{{ route('register') }}" class="space-y-4 md:space-y-6">
          @csrf

          <!-- Name -->
          <div>
            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Your name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="John Doe" required autofocus autocomplete="name">
            @if ($errors->has('name'))
              <p class="mt-2 text-sm text-red-600">{{ $errors->first('name') }}</p>
            @endif
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Your email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" placeholder="name@company.com" required autocomplete="username">
            @if ($errors->has('email'))
              <p class="mt-2 text-sm text-red-600">{{ $errors->first('email') }}</p>
            @endif
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required autocomplete="new-password">
            @if ($errors->has('password'))
              <p class="mt-2 text-sm text-red-600">{{ $errors->first('password') }}</p>
            @endif
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5" required autocomplete="new-password">
            @if ($errors->has('password_confirmation'))
              <p class="mt-2 text-sm text-red-600">{{ $errors->first('password_confirmation') }}</p>
            @endif
          </div>

         

          <!-- Submit Button -->
          <button type="submit"  class="w-full text-white transition bg-black rounded-md hover:text-white hover:bg-gray-800 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Create an account</button>

          <!-- Login Link -->
          <p class="text-sm font-light text-gray-500">
            Already have an account? <a href="{{ route('login') }}" class="font-medium text-primary-600 hover:underline">Login here</a>
          </p>
        </form>
      </div>


</x-guest-layout>
