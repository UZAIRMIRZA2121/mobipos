<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Welcome back! Please enter your email and the 6-digit OTP provided by the shop admin to login as Staff.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    
    @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600 dark:text-red-400">
                {{ __('Whoops! Something went wrong.') }}
            </div>
            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.login.post') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Staff Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- OTP -->
        <div class="mt-4">
            <x-input-label for="otp" :value="__('6-Digit OTP')" />
            <x-text-input id="otp" class="block mt-1 w-full"
                            type="text"
                            name="otp"
                            required autocomplete="off" />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Back to Owner Login') }}
            </a>

            <x-primary-button class="ms-3">
                {{ __('Staff Login') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
