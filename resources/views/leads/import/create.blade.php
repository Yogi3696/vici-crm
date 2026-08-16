<x-app-layout>
    <x-slot name="header">
        {{ __('Upload Leads') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Import leads into a list from a CSV file.') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Leads') }}
        </a>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4">
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form action="{{ route('leads.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="file" class="form-label">{{ __('CSV file') }}</label>
                            <input type="file" name="file" id="file" accept=".csv,text/csv,text/plain"
                                   class="form-control @error('file') is-invalid @enderror" required>
                            <div class="form-text">{{ __('Up to 20 MB. Comma, semicolon, tab or pipe separated.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="list_id" class="form-label">{{ __('Import into list') }}</label>
                            <select name="list_id" id="list_id"
                                    class="form-select @error('list_id') is-invalid @enderror" required>
                                <option value="">{{ __('Choose a list...') }}</option>
                                @foreach ($lists as $list)
                                    <option value="{{ $list->list_id }}" @selected(old('list_id') == $list->list_id)>
                                        {{ $list->list_name ?: $list->list_id }} (#{{ $list->list_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="delimiter" class="form-label">{{ __('Delimiter') }}</label>
                                <select name="delimiter" id="delimiter" class="form-select">
                                    <option value="comma" @selected(old('delimiter', 'comma') === 'comma')>{{ __('Comma  (,)') }}</option>
                                    <option value="semicolon" @selected(old('delimiter') === 'semicolon')>{{ __('Semicolon  (;)') }}</option>
                                    <option value="tab" @selected(old('delimiter') === 'tab')>{{ __('Tab') }}</option>
                                    <option value="pipe" @selected(old('delimiter') === 'pipe')>{{ __('Pipe  (|)') }}</option>
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label for="status" class="form-label">{{ __('Status for new leads') }}</label>
                                <select name="status" id="status"
                                        class="form-select @error('status') is-invalid @enderror">
                                    @foreach ($statuses as $option)
                                        <option value="{{ $option->status }}" @selected(old('status', 'NEW') === $option->status)>
                                            {{ $option->status }} &mdash; {{ $option->status_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">{{ __('NEW is the standard status for leads waiting to be dialled.') }}</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="duplicate_check" class="form-label">{{ __('Duplicate check') }}</label>
                                <select name="duplicate_check" id="duplicate_check" class="form-select">
                                    <option value="none" @selected(old('duplicate_check', 'none') === 'none')>{{ __('Do not check for duplicates') }}</option>
                                    <option value="list" @selected(old('duplicate_check') === 'list')>{{ __('Skip phone numbers already in this list') }}</option>
                                    <option value="all" @selected(old('duplicate_check') === 'all')>{{ __('Skip phone numbers already in any list') }}</option>
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label for="phone_code" class="form-label">
                                    {{ __('Default phone code') }}
                                    <span class="text-secondary fw-normal">{{ __('(optional)') }}</span>
                                </label>
                                <input type="text" name="phone_code" id="phone_code" maxlength="10"
                                       class="form-control" value="{{ old('phone_code') }}"
                                       placeholder="{{ __('e.g. 91') }}">
                                <div class="form-text">{{ __('Used only where the file has no phone code.') }}</div>
                            </div>
                        </div>

                        <fieldset class="mb-4">
                            <legend class="form-label">{{ __('Options') }}</legend>

                            <div class="form-check">
                                <input type="hidden" name="has_header" value="0">
                                <input class="form-check-input" type="checkbox" name="has_header" id="has_header"
                                       value="1" @checked(old('has_header', '1') == '1')>
                                <label class="form-check-label" for="has_header">
                                    {{ __('First row contains column names') }}
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="hidden" name="reset_dialable" value="0">
                                <input class="form-check-input" type="checkbox" name="reset_dialable" id="reset_dialable"
                                       value="1" @checked(old('reset_dialable', '1') == '1')>
                                <label class="form-check-label" for="reset_dialable">
                                    {{ __('Make leads dialable straight away') }}
                                    <span class="d-block form-text mt-0">{{ __('Leave on so the dialer picks them up without a list reset.') }}</span>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="hidden" name="skip_invalid_phone" value="0">
                                <input class="form-check-input" type="checkbox" name="skip_invalid_phone" id="skip_invalid_phone"
                                       value="1" @checked(old('skip_invalid_phone', '1') == '1')>
                                <label class="form-check-label" for="skip_invalid_phone">
                                    {{ __('Skip rows with an unusable phone number') }}
                                    <span class="d-block form-text mt-0">{{ __('Drops numbers shorter than 7 or longer than 18 digits.') }}</span>
                                </label>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>{{ __('Continue to mapping') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body p-4">
                    <h2 class="cell-title mb-2">{{ __('How it works') }}</h2>
                    <ol class="text-secondary small ps-3 mb-0">
                        <li class="mb-2">{{ __('Pick your CSV file and the list it should load into.') }}</li>
                        <li class="mb-2">{{ __('Match each file column to a lead field on the next screen.') }}</li>
                        <li class="mb-2">{{ __('Phone Number is required; every other field is optional.') }}</li>
                        <li class="mb-2">{{ __('Leads land with the status you choose — NEW means waiting to be dialled.') }}</li>
                        <li>{{ __('Skipped rows are counted and reported back by reason.') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
