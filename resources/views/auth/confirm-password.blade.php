<x-guest-layout>
    <x-auth-card :title="__('Confirm password')" :subtitle="__('This is a secure area of the application')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" />
            </a>
        </x-slot>

        <x-auth-validation-errors :errors="$errors" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-4">
                <x-label for="password" :value="__('Password')" />
                <x-input id="password" type="password" name="password" required autofocus autocomplete="current-password" />
            </div>

            <x-button class="w-100 py-2">
                {{ __('Confirm') }}
            </x-button>
        </form>
    </x-auth-card>
</x-guest-layout>
