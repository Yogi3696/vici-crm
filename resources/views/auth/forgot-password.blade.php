<x-guest-layout>
    <x-auth-card :title="__('Forgot password?')" :subtitle="__('We will email you a link to reset it')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" style="fill:currentColor" />
            </a>
        </x-slot>

        <x-auth-session-status :status="session('status')" />

        <x-auth-validation-errors :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <x-label for="email" :value="__('Email')" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <x-button class="w-100 py-2">
                {{ __('Email Password Reset Link') }}
            </x-button>
        </form>

        <p class="text-center small mt-4 mb-0">
            <a class="text-navy" href="{{ route('login') }}">{{ __('Back to log in') }}</a>
        </p>
    </x-auth-card>
</x-guest-layout>
