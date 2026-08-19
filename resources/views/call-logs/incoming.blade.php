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

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search || $status || $campaignId)
            <a href="{{ route('call-logs.incoming') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ number_format($logs->total()) }} {{ Str::plural('log', $logs->total()) }}
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Date / Time') }}</th>
                            <th>{{ __('Phone Number') }}</th>
                            <th>{{ __('Lead ID') }}</th>
                            <th>{{ __('Campaign') }}</th>
                            <th>{{ __('List ID') }}</th>
                            <th>{{ __('Length (s)') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('User') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-secondary">{{ $log->call_date }}</td>
                                <td>
                                    <span class="cell-mono">{{ $log->phone_number }}</span>
                                </td>
                                <td>#{{ $log->lead_id }}</td>
                                <td>
                                    <span class="pill pill-tag">{{ $log->campaign_id }}</span>
                                </td>
                                <td>
                                    <span class="pill pill-tag">{{ $log->list_id }}</span>
                                </td>
                                <td class="text-end text-secondary">{{ $log->length_in_sec }}</td>
                                <td>
                                    <span class="pill pill-status" title="{{ $log->status }}">
                                        {{ $log->vicidialStatus ? $log->vicidialStatus->status_name : $log->status }}
                                    </span>
                                </td>
                                <td>{{ $log->user }}</td>
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
