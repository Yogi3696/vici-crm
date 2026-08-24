<x-app-layout>
    <x-slot name="header">
        {{ __('Incoming Call Logs') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Browse and filter incoming call logs.') }}
    </x-slot>

    <form action="{{ route('call-logs.incoming') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group input-group-search">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control ps-0"
                       placeholder="{{ __('Search by phone, campaign...') }}" value="{{ $search }}">
            </div>
        </div>

        <div class="toolbar-filter">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option['status'] }}" @selected($status === $option['status'])>
                        {{ $option['label'] }} ({{ number_format($option['total']) }})
                    </option>
                @endforeach
            </select>
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
            <select name="agent" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All agents') }}</option>
                @foreach ($agents as $option)
                    <option value="{{ $option['user'] }}" @selected((string) $agent === (string) $option['user'])>
                        {{ $option['label'] }} ({{ number_format($option['total']) }})
                    </option>
                @endforeach
                @if($noAgentTotal)
                    <option value="{{ App\Models\VicidialCloserLog::NO_AGENT_USER }}"
                            @selected($agent === App\Models\VicidialCloserLog::NO_AGENT_USER)>
                        {{ __('No agent (unhandled)') }} ({{ number_format($noAgentTotal) }})
                    </option>
                @endif
            </select>
        </div>

        <div class="toolbar-filter">
            <select name="missed" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All calls') }}</option>
                <option value="yes" @selected($missed === 'yes')>{{ __('Missed only') }}</option>
                <option value="no" @selected($missed === 'no')>{{ __('Answered only') }}</option>
            </select>
        </div>

        <div class="toolbar-filter">
            <input type="date" name="from_date" class="form-control"
                   title="{{ __('Call date from') }}" placeholder="{{ __('From') }}"
                   value="{{ $fromDate }}" max="{{ $toDate }}">
        </div>

        <div class="toolbar-filter">
            <input type="date" name="to_date" class="form-control"
                   title="{{ __('Call date to') }}" placeholder="{{ __('To') }}"
                   value="{{ $toDate }}" min="{{ $fromDate }}">
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search || $status || $campaignId || $fromDate || $toDate || $missed || $agent)
            <a href="{{ route('call-logs.incoming') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ number_format($logs->total()) }} {{ Str::plural('log', $logs->total()) }}
            @if($missedCount)
                &middot; <span class="text-danger">{{ number_format($missedCount) }} {{ __('missed') }}</span>
            @endif
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <x-sort-header column="lead_id" :label="__('Lead ID')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="call_date" :label="__('Date / Time')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="phone_number" :label="__('Phone Number')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="campaign_id" :label="__('Campaign')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="list_id" :label="__('List')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="status" :label="__('Status')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="user" :label="__('User')" :sort="$sort" :direction="$direction" />
                            <x-sort-header column="length_in_sec" :label="__('Length (min)')" :sort="$sort" :direction="$direction" align="end" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>
                                    @if($log->lead_id)
                                        <span class="cell-lead-id">#{{ $log->lead_id }}</span>
                                    @else
                                        {{-- Vicidial logs 0 when the inbound call matched no lead --}}
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-secondary">{{ $log->call_date }}</td>
                                <td>
                                    <span class="cell-mono">{{ $log->phone_number }}</span>
                                </td>
                                <td>
                                    <span class="pill pill-tag">{{ $log->campaign_id }}</span>
                                </td>
                                <td>
                                    <span class="pill pill-tag" title="{{ __('List ID') }}: {{ $log->list_id }}">
                                        {{ optional($log->vicidialList)->list_name ?: $log->list_id }}
                                    </span>
                                </td>
                                <td>
                                    <span class="pill pill-status" title="{{ $log->status }}">
                                        {{ $log->vicidialStatus ? $log->vicidialStatus->status_name : $log->status }}
                                    </span>
                                    @if($log->is_missed)
                                        <span class="pill pill-missed">{{ __('Missed') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->has_no_agent)
                                        <span class="text-secondary">{{ __('No agent') }}</span>
                                    @else
                                        {{ $log->user }}
                                    @endif
                                </td>
                                <td class="text-end text-secondary" title="{{ $log->length_in_sec }} {{ __('seconds') }}">
                                    <span class="cell-mono">{{ $log->length_in_minutes }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-telephone-inbound"></i></div>
                                        <p class="empty-title">{{ __('No call logs found') }}</p>
                                        <p class="empty-text">{{ __('Try adjusting your search.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
