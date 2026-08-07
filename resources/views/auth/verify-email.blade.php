<x-guest-layout>
    <x-auth-card :title="__('Verify your email')">
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="auth-logo" style="fill:currentColor" />
            </a>
        </x-slot>

        <p class="text-muted small">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success py-2 small">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
            @csrf
            <x-button class="w-100 py-2">
                {{ __('Resend Verification Email') }}
            </x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-navy text-decoration-none">
                {{ __('Log Out') }}
            </button>
        </form>
    </x-auth-card>
</x-guest-layout>
