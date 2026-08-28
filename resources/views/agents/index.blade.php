<x-app-layout>
    <x-slot name="header">
        {{ __('Agents') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Manage system agents (User Level 1).') }}
    </x-slot>

    <form action="{{ route('agents.index') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group input-group-search">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control ps-0"
                       placeholder="{{ __('Search agents...') }}" value="{{ $search }}">
            </div>
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search)
            <a href="{{ route('agents.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ $agents->total() }} {{ Str::plural('agent', $agents->total()) }}
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Agent') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Full Name') }}</th>
                            <th>{{ __('User Group') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            <tr>
                                <td>
                                    <div class="cell-title">{{ $agent->user }}</div>
                                    <div class="cell-id">ID: {{ $agent->user_id }}</div>
                                </td>
                                <td>
                                    @if($agent->active === 'Y')
                                        <span class="pill pill-active"><span class="pill-dot"></span>{{ __('Active') }}</span>
                                    @else
                                        <span class="pill pill-inactive"><span class="pill-dot"></span>{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $agent->full_name ?: '—' }}
                                </td>
                                <td>
                                    <span class="pill pill-tag">
                                        <i class="bi bi-people"></i>{{ $agent->user_group ?: '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-headset"></i></div>
                                        <p class="empty-title">{{ __('No agents found') }}</p>
                                        <p class="empty-text">{{ __('Try adjusting your search.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($agents->hasPages())
            <div class="card-footer">
                {{ $agents->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
