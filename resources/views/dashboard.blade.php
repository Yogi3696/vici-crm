<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => __('Total Leads'), 'value' => '0', 'icon' => 'bi-people'],
            ['label' => __('Calls Today'), 'value' => '0', 'icon' => 'bi-telephone'],
            ['label' => __('Active Agents'), 'value' => '0', 'icon' => 'bi-headset'],
            ['label' => __('Campaigns'), 'value' => '0', 'icon' => 'bi-megaphone'],
        ] as $tile)
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-tile h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">{{ $tile['label'] }}</div>
                            <div class="stat-value">{{ $tile['value'] }}</div>
                        </div>
                        <div class="stat-icon">
                            <i class="bi {{ $tile['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header">{{ __('Recent Activity') }}</div>
                <div class="card-body">
                    <p class="text-muted mb-0">{{ __("You're logged in!") }}</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">{{ __('Quick Actions') }}</div>
                <div class="card-body d-grid gap-2">
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>{{ __('Add Lead') }}
                    </button>
                    <button type="button" class="btn btn-outline-primary">
                        <i class="bi bi-upload me-1"></i>{{ __('Import Leads') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-1"></i>{{ __('Settings') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
