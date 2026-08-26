<x-app-layout>
    <x-slot name="header">
        {{ __('Lists') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Lead lists and the campaigns they dial for.') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('leads.import.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i>{{ __('Upload Leads') }}
        </a>
    </x-slot>

    <form action="{{ route('lists.index') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group input-group-search">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control ps-0"
                       placeholder="{{ __('List id, name or description...') }}" value="{{ $search }}">
            </div>
        </div>

        <div class="toolbar-filter">
            <select name="campaign_id" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All campaigns') }}</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->campaign_id }}" @selected((string) $campaignId === (string) $campaign->campaign_id)>
                        {{ $campaign->campaign_name ?: $campaign->campaign_id }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="toolbar-filter">
            <select name="active" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All lists') }}</option>
                <option value="Y" @selected($active === 'Y')>{{ __('Active only') }}</option>
                <option value="N" @selected($active === 'N')>{{ __('Inactive only') }}</option>
            </select>
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search || $campaignId || $active)
            <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ number_format($lists->total()) }} {{ Str::plural('list', $lists->total()) }}
            @if($activeTotal)
                &middot; {{ number_format($activeTotal) }} {{ __('active') }}
            @endif
            @if($leadTotal)
                &middot; {{ number_format($leadTotal) }} {{ Str::plural('lead', $leadTotal) }}
            @endif
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <x-sort-header column="list_id" :label="__('List ID')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="list_name" :label="__('List')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="campaign_id" :label="__('Campaign')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="active" :label="__('Status')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="leads_count" :label="__('Leads')" :sort="$sort" :direction="$direction" align="end" />
                            <x-sort-header column="list_lastcalldate" :label="__('Last Call')" :sort="$sort" :direction="$direction" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lists as $list)
                            <tr>
                                <td>
                                    <span class="cell-lead-id">#{{ $list->list_id }}</span>
                                </td>
                                <td>
                                    <div class="cell-title">{{ $list->list_name ?: __('(no name)') }}</div>
                                    @if($list->list_description)
                                        <div class="cell-id">{{ $list->list_description }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($list->campaign_id)
                                        <span class="pill pill-tag" title="{{ __('Campaign ID') }}: {{ $list->campaign_id }}">
                                            {{ optional($list->campaign)->campaign_name ?: $list->campaign_id }}
                                        </span>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($list->is_active)
                                        <span class="pill pill-active"><span class="pill-dot"></span>{{ __('Active') }}</span>
                                    @else
                                        <span class="pill pill-inactive"><span class="pill-dot"></span>{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($list->leads_count)
                                        <a href="{{ route('leads.index', ['list_id' => $list->list_id]) }}"
                                           class="pill pill-tag" title="{{ __('View these leads') }}">
                                            {{ number_format($list->leads_count) }}
                                        </a>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-secondary">
                                    @if($list->list_lastcalldate)
                                        <span title="{{ $list->list_lastcalldate }}">
                                            {{ \Carbon\Carbon::parse($list->list_lastcalldate)->format('d M Y, H:i') }}
                                        </span>
                                    @else
                                        <span class="pill-muted">{{ __('Never') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-list-ul"></i></div>
                                        <p class="empty-title">{{ __('No lists found') }}</p>
                                        <p class="empty-text">{{ __('Try adjusting your search.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($lists->hasPages())
            <div class="card-footer">
                {{ $lists->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
