<x-guest-layout>
    <x-auth-card :title="__('Create an account')" :subtitle="__('Get started in just a few seconds')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" style="fill:currentColor" />
            </a>
        </x-slot>

        <x-auth-validation-errors :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <x-label for="name" :value="__('Name')" />
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <div class="mb-3">
                <x-label for="email" :value="__('Email')" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mb-3">
                <x-label for="password" :value="__('Password')" />
                <x-input id="password" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mb-4">
                <x-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            <x-button class="w-100 py-2">
                {{ __('Register') }}
            </x-button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            {{ __('Already registered?') }}
            <a class="text-navy fw-medium" href="{{ route('login') }}">{{ __('Log in') }}</a>
        </p>
    </x-auth-card>
</x-guest-layout>
