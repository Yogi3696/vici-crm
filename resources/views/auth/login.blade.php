<x-guest-layout>
    <x-auth-card :title="__('Welcome back')" :subtitle="__('Sign in to continue to your dashboard')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" />
            </a>
        </x-slot>

        <x-auth-session-status :status="session('status')" />

        <x-auth-validation-errors :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <x-label for="email" :value="__('Email')" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mb-3">
                <x-label for="password" :value="__('Password')" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                    <label class="form-check-label small" for="remember_me">{{ __('Remember me') }}</label>
                </div>

                @if (Route::has('password.request'))
                    <a class="small text-navy" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <x-button class="w-100 py-2">
                {{ __('Log in') }}
            </x-button>
        </form>

        @if (Route::has('register'))
            <p class="text-center text-muted small mt-4 mb-0">
                {{ __("Don't have an account?") }}
                <a class="text-navy fw-medium" href="{{ route('register') }}">{{ __('Register') }}</a>
            </p>
        @endif
    </x-auth-card>
</x-guest-layout>
