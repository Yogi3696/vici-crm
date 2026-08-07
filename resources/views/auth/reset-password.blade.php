<x-guest-layout>
    <x-auth-card :title="__('Reset password')" :subtitle="__('Choose a new password for your account')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" style="fill:currentColor" />
            </a>
        </x-slot>

        <x-auth-validation-errors :errors="$errors" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <x-label for="email" :value="__('Email')" />
                <x-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus />
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
                {{ __('Reset Password') }}
            </x-button>
        </form>
    </x-auth-card>
</x-guest-layout>
