<x-app-layout>
    <x-slot name="header">
        {{ __('Agents') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Manage system agents.') }}
    </x-slot>

    <div class="card card-table">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-headset"></i></div>
                <p class="empty-title">{{ __('Agents List') }}</p>
                <p class="empty-text">{{ __('Agent functionality goes here.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
