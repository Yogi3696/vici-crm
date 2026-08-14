<x-app-layout>
    <x-slot name="header">
        {{ __('Inbound Groups') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Inbound queues and the campaigns that route to them.') }}
    </x-slot>

    <form action="{{ route('inbound-groups.index') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-secondary">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0"
                       placeholder="{{ __('Search groups...') }}" value="{{ $search }}">
            </div>
        </div>

        <div class="toolbar-filter">
            <select name="campaign_id" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All campaigns') }}</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->campaign_id }}" @selected($campaignId === $campaign->campaign_id)>
                        {{ $campaign->campaign_name ?: $campaign->campaign_id }} ({{ count($campaign->inbound_group_ids) }})
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search || $campaignId)
            <a href="{{ route('inbound-groups.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            @if($selectedCampaign)
                {{ $inboundGroups->total() }} {{ Str::plural('group', $inboundGroups->total()) }}
                {{ __('in') }} <strong class="text-navy">{{ $selectedCampaign->campaign_name ?: $selectedCampaign->campaign_id }}</strong>
            @else
                {{ $inboundGroups->total() }} {{ Str::plural('group', $inboundGroups->total()) }}
            @endif
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Group') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Campaigns') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inboundGroups as $group)
                            <tr>
                                <td>
                                    <div class="cell-title">{{ $group->group_name ?: $group->group_id }}</div>
                                    <div class="cell-id">{{ $group->group_id }}</div>
                                </td>
                                <td>
                                    @if($group->active === 'Y')
                                        <span class="pill pill-active"><span class="pill-dot"></span>{{ __('Active') }}</span>
                                    @else
                                        <span class="pill pill-inactive"><span class="pill-dot"></span>{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @forelse ($campaignsByGroup[$group->group_id] ?? [] as $mappedCampaign)
                                        <a href="{{ route('inbound-groups.index', ['campaign_id' => $mappedCampaign]) }}"
                                           class="pill pill-tag me-1">{{ $mappedCampaign }}</a>
                                    @empty
                                        <span class="pill-muted">&mdash;</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-telephone-inbound"></i></div>
                                        <p class="empty-title">{{ __('No inbound groups found') }}</p>
                                        <p class="empty-text">
                                            @if($selectedCampaign)
                                                {{ __('This campaign has no inbound groups mapped to it.') }}
                                            @else
                                                {{ __('Try adjusting your search.') }}
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($inboundGroups->hasPages())
            <div class="card-footer">
                {{ $inboundGroups->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
