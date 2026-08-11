<x-app-layout>
    <x-slot name="header">
        {{ __('Campaigns') }}
    </x-slot>

    <div class="container-fluid px-4 py-4">
        <div class="card shadow-sm border border-primary rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-primary border-bottom border-primary">
                            <tr>
                                <th class="py-3 ps-4 fw-semibold">ID</th>
                                <th class="py-3 fw-semibold">Name</th>
                                <th class="py-3 fw-semibold">Status</th>
                                <th class="py-3 fw-semibold">Active</th>
                                <th class="py-3 pe-4 fw-semibold">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($campaigns as $campaign)
                                <tr>
                                    <td class="ps-4 fw-medium text-secondary">{{ $campaign->campaign_id }}</td>
                                    <td class="fw-medium text-dark">{{ $campaign->campaign_name }}</td>
                                    <td>
                                        @if($campaign->active == 'Y')
                                            <span class="badge bg-success-subtle text-success border border-success rounded-pill px-3 py-2">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-3 py-2">Inactive</span>
                                        @endif
                                    </td>
                                    <td><span class="fw-medium">{{ $campaign->active }}</span></td>
                                    <td class="pe-4 text-muted">{{ $campaign->campaign_description ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox fs-1 mb-2 text-secondary"></i>
                                            <span class="fw-medium">No campaigns found.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($campaigns->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
