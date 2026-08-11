<x-guest-layout>
    <div class="flex justify-center mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" class="h-28 w-auto">
            <defs>
                <linearGradient id="phoneGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#2563EB" />
                    <stop offset="100%" stop-color="#1E3A8A" />
                </linearGradient>
                <linearGradient id="screenGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#06B6D4" />
                    <stop offset="100%" stop-color="#0284C7" />
                </linearGradient>
                <linearGradient id="cartGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#F97316" />
                    <stop offset="100%" stop-color="#F59E0B" />
                </linearGradient>
                <filter id="shadow" x="-10%" y="-10%" width="130%" height="130%">
                    <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.2"/>
                </filter>
            </defs>

            <!-- Phone Body -->
            <rect x="40" y="25" width="85" height="150" rx="18" fill="url(#phoneGrad)" filter="url(#shadow)" />
            
            <!-- Phone Screen -->
            <rect x="48" y="35" width="69" height="115" rx="8" fill="url(#screenGrad)" />
            
            <!-- Screen UI Elements (Abstract graph/lines) -->
            <path d="M48 105 Q65 85, 80 100 T 117 75" fill="none" stroke="#BAE6FD" stroke-width="3" stroke-linecap="round" opacity="0.5"/>
            <path d="M48 120 Q65 100, 80 115 T 117 90" fill="none" stroke="#7DD3FC" stroke-width="3" stroke-linecap="round" opacity="0.3"/>
            
            <!-- Home Button -->
            <circle cx="82.5" cy="160" r="5" fill="#BFDBFE" opacity="0.7"/>
            <path d="M72.5 30 H92.5" stroke="#BFDBFE" stroke-width="3" stroke-linecap="round" opacity="0.5"/>

            <!-- Floating Cart Icon with smooth curves -->
            <g filter="url(#shadow)">
                <path d="M60 90 L85 140 H140 L160 90 Z" fill="#FFFFFF" opacity="0.95"/>
                <!-- Cart Handle/Frame -->
                <path d="M45 70 H65 L85 140 H140 L160 90 H70" fill="none" stroke="url(#cartGrad)" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
                
                <!-- Receipt Paper -->
                <path d="M110 50 V90 H145 V50 Z" fill="#F8FAFC" stroke="#E2E8F0" stroke-width="2" stroke-linejoin="round"/>
                <!-- Receipt Lines -->
                <line x1="118" y1="62" x2="137" y2="62" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="118" y1="72" x2="137" y2="72" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="118" y1="82" x2="128" y2="82" stroke="#94A3B8" stroke-width="2.5" stroke-linecap="round"/>

                <!-- Wheels -->
                <circle cx="95" cy="155" r="9" fill="#EA580C" />
                <circle cx="95" cy="155" r="4" fill="#FFFFFF" />
                <circle cx="135" cy="155" r="9" fill="#EA580C" />
                <circle cx="135" cy="155" r="4" fill="#FFFFFF" />
            </g>
            
            <!-- Sparkles/Accents for premium feel -->
            <circle cx="170" cy="50" r="4" fill="#FDE047" filter="url(#shadow)"/>
            <circle cx="150" cy="35" r="2.5" fill="#FCD34D" />
            <circle cx="35" cy="80" r="3" fill="#60A5FA" />
        </svg>
    </div>

    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Your trial account has been ended. Please contact to the admin support via WhatsApp: ') }} <strong>03086452242</strong>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('trial.verify') }}">
        @csrf

        <!-- OTP Input -->
        <div>
            <x-input-label for="otp" :value="__('Enter OTP to approve account')" />
            <x-text-input id="otp" class="block mt-1 w-full" type="text" name="otp" required autofocus />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            @php
                $lastOtpTime = session('last_otp_time');
                $remainingSeconds = 0;
                if ($lastOtpTime) {
                    $diff = now()->diffInSeconds($lastOtpTime);
                    if ($diff < 60) {
                        $remainingSeconds = (int) (60 - $diff);
                    }
                }
            @endphp
            <button type="submit" form="resend-form" id="resend-btn"
                @if($remainingSeconds > 0) disabled @endif
                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="resend-text">{{ __('Generate New OTP') }}</span>
                <span id="resend-timer" class="{{ $remainingSeconds > 0 ? '' : 'hidden' }}">({{ $remainingSeconds }}s)</span>
            </button>
            <x-primary-button class="ms-3">
                {{ __('Verify OTP') }}
            </x-primary-button>
        </div>
    </form>
    
    <form id="resend-form" method="POST" action="{{ route('trial.resend') }}" class="hidden">
        @csrf
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
            {{ __('Log Out') }}
        </button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let remainingSeconds = {{ $remainingSeconds }};
            if (remainingSeconds > 0) {
                const btn = document.getElementById('resend-btn');
                const timerSpan = document.getElementById('resend-timer');
                
                const interval = setInterval(() => {
                    remainingSeconds--;
                    if (remainingSeconds <= 0) {
                        clearInterval(interval);
                        btn.disabled = false;
                        timerSpan.classList.add('hidden');
                    } else {
                        timerSpan.textContent = `(${remainingSeconds}s)`;
                    }
                }, 1000);
            }
        });
    </script>
</x-guest-layout>
