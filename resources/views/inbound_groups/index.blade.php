<x-app-layout>
    <x-slot name="header">
        {{ __('Inbound Groups') }}
    </x-slot>

    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('inbound-groups.index') }}" method="GET" class="d-flex" style="max-width: 400px; width: 100%;">
                <div class="input-group shadow-sm">
                    <input type="text" name="search" class="form-control border-primary" placeholder="Search inbound groups..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Search</button>
                    @if(request('search'))
                        <a href="{{ route('inbound-groups.index') }}" class="btn btn-outline-danger">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="bg-light text-secondary border-bottom">
                            <tr>
                                <th class="py-3 ps-4 fw-semibold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">ID</th>
                                <th class="py-3 fw-semibold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Name</th>
                                <th class="py-3 fw-semibold text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 1px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inboundGroups as $group)
                                <tr class="border-bottom" style="transition: background-color 0.2s ease;">
                                    <td class="ps-4 fw-medium text-secondary">
                                        <span class="text-muted">#</span>{{ $group->group_id }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center py-2">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-telephone-inbound"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark">{{ $group->group_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($group->active == 'Y')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 fw-semibold">Active</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-2 fw-semibold">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                <i class="bi bi-inbox fs-1 text-secondary"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1">No inbound groups found</h5>
                                            <p class="mb-0 small">Try adjusting your search query.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($inboundGroups->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $inboundGroups->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
