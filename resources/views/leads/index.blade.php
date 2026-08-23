<x-app-layout>
    <x-slot name="header">
        {{ __('Leads') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Browse and filter leads across all lists.') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('leads.import.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i>{{ __('Upload Leads') }}
        </a>
    </x-slot>

    <form action="{{ route('leads.index') }}" method="GET" class="toolbar">
        <div class="toolbar-search">
            <div class="input-group input-group-search">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control ps-0"
                       placeholder="{{ __('Name, phone or email...') }}" value="{{ $search }}">
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
            <select name="list_id" class="form-select" onchange="this.form.submit()">
                <option value="">{{ __('All lists') }}</option>
                @foreach ($lists as $list)
                    <option value="{{ $list->list_id }}" @selected((string) $listId === (string) $list->list_id)>
                        {{ $list->list_name ?: $list->list_id }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>

        @if($search || $status || $listId)
            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        @endif

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ number_format($leads->total()) }} {{ Str::plural('lead', $leads->total()) }}
        </div>
    </form>

    <div class="card card-table">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Lead ID') }}</th>
                            <th>{{ __('Lead') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('List') }}</th>
                            <th class="text-end">{{ __('Calls') }}</th>
                            <th>{{ __('Last Modified') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <span class="cell-lead-id">#{{ $lead->lead_id }}</span>
                                </td>
                                <td>
                                    <div class="cell-title">{{ $lead->full_name ?: __('(no name)') }}</div>
                                    @if($lead->email)
                                        <div class="cell-id">{{ $lead->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->phone_number)
                                        <span class="cell-mono">{{ $lead->phone_number }}</span>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->status)
                                        <span class="pill pill-status"
                                              title="{{ $lead->status }}">{{ optional($lead->statusDetail)->status_name ?: $lead->status }}</span>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->list)
                                        <a href="{{ route('leads.index', ['list_id' => $lead->list_id]) }}"
                                           class="pill pill-tag">{{ $lead->list->list_name ?: $lead->list_id }}</a>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-end text-secondary">{{ number_format($lead->called_count) }}</td>
                                <td class="text-secondary">
                                    @if($lead->modify_date)
                                        <span title="{{ $lead->modify_date->toDayDateTimeString() }}">
                                            {{ $lead->modify_date->format('d M Y, H:i') }}
                                        </span>
                                    @else
                                        <span class="pill-muted">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="bi bi-people"></i></div>
                                        <p class="empty-title">{{ __('No leads found') }}</p>
                                        <p class="empty-text">{{ __('Try adjusting your search or filters.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($leads->hasPages())
            <div class="card-footer">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
