<x-app-layout>
    <x-slot name="header">
        {{ __('Create New List') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Add a lead list for a campaign to dial.') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Lists') }}
        </a>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form action="{{ route('lists.store') }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <label for="list_id" class="form-label">{{ __('List ID') }}</label>
                                <input type="number" name="list_id" id="list_id" min="1"
                                       class="form-control @error('list_id') is-invalid @enderror"
                                       value="{{ old('list_id', $suggestedId) }}" required>
                                <div class="form-text">{{ __('Must be unique. Vicidial does not assign this automatically.') }}</div>
                            </div>

                            <div class="col-sm-8">
                                <label for="list_name" class="form-label">{{ __('List name') }}</label>
                                <input type="text" name="list_name" id="list_name" maxlength="30"
                                       class="form-control @error('list_name') is-invalid @enderror"
                                       value="{{ old('list_name') }}" required>
                                <div class="form-text">{{ __('Up to 30 characters.') }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-8">
                                <label for="campaign_id" class="form-label">{{ __('Campaign') }}</label>
                                <select name="campaign_id" id="campaign_id"
                                        class="form-select @error('campaign_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Choose a campaign...') }}</option>
                                    @foreach ($campaigns as $campaign)
                                        <option value="{{ $campaign->campaign_id }}" @selected(old('campaign_id') === $campaign->campaign_id)>
                                            {{ $campaign->campaign_name ?: $campaign->campaign_id }} ({{ $campaign->campaign_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label for="active" class="form-label">{{ __('Status') }}</label>
                                <select name="active" id="active"
                                        class="form-select @error('active') is-invalid @enderror" required>
                                    <option value="Y" @selected(old('active', 'Y') === 'Y')>{{ __('Active') }}</option>
                                    <option value="N" @selected(old('active') === 'N')>{{ __('Inactive') }}</option>
                                </select>
                                <div class="form-text">{{ __('Only active lists are dialled.') }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="list_description" class="form-label">
                                {{ __('Description') }} <span class="text-secondary">{{ __('(optional)') }}</span>
                            </label>
                            <input type="text" name="list_description" id="list_description" maxlength="255"
                                   class="form-control @error('list_description') is-invalid @enderror"
                                   value="{{ old('list_description') }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i>{{ __('Create List') }}
                            </button>
                            <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
