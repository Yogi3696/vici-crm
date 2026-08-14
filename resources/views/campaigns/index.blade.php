<x-app-layout>
    <x-slot name="header">
        {{ __('Campaigns') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Dialer campaigns and their inbound group mappings.') }}
    </x-slot>

    <form action="{{ route('campaigns.index') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0"
                       placeholder="{{ __('Search campaigns...') }}" value="{{ $search }}">
            </div>
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search)
            <a href="{{ route('campaigns.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ $campaigns->total() }} {{ Str::plural('campaign', $campaigns->total()) }}
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Campaign') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Inbound Groups') }}</th>
                            <th>{{ __('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div class="cell-title">{{ $campaign->campaign_name ?: $campaign->campaign_id }}</div>
                                    <div class="cell-id">{{ $campaign->campaign_id }}</div>
                                </td>
                                <td>
                                    @if($campaign->active === 'Y')
                                        <span class="pill pill-active"><span class="pill-dot"></span>{{ __('Active') }}</span>
                                    @else
                                        <span class="pill pill-inactive"><span class="pill-dot"></span>{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php $groupCount = count($campaign->inbound_group_ids); @endphp
                                    @if($groupCount)
                                        <a href="{{ route('inbound-groups.index', ['campaign_id' => $campaign->campaign_id]) }}"
                                           class="pill pill-tag">
                                            <i class="bi bi-telephone-inbound"></i>{{ $groupCount }}
                                        </a>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    <span class="d-inline-block text-truncate" style="max-width: 22rem;"
                                          title="{{ $campaign->campaign_description }}">
                                        {{ $campaign->campaign_description ?: '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-megaphone"></i></div>
                                        <p class="empty-title">{{ __('No campaigns found') }}</p>
                                        <p class="empty-text">{{ __('Try adjusting your search.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($campaigns->hasPages())
            <div class="card-footer">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
