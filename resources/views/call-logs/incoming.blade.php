<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            {{ __('Incoming Call Logs') }}
        </h2>
    </x-slot>

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-primary" style="border-radius: 0.5rem;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <form action="{{ route('call-logs.incoming') }}" method="GET" class="d-flex w-100 max-w-md" style="max-width: 400px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by phone, campaign..." value="{{ $search }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Date / Time</th>
                                <th>Phone Number</th>
                                <th>Lead ID</th>
                                <th>Campaign</th>
                                <th>List ID</th>
                                <th>Length (s)</th>
                                <th>Status</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $log->call_date }}</td>
                                    <td>{{ $log->phone_number }}</td>
                                    <td>{{ $log->lead_id }}</td>
                                    <td>{{ $log->campaign_id }}</td>
                                    <td>{{ $log->list_id }}</td>
                                    <td>{{ $log->length_in_sec }}</td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $log->status }}
                                        </span>
                                        @if($log->vicidialStatus)
                                            <small class="text-muted d-block">{{ $log->vicidialStatus->status_name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->user }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No incoming call logs found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
