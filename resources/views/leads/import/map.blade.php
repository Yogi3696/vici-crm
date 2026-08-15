<x-app-layout>
    <x-slot name="header">
        {{ __('Map Columns') }}
    </x-slot>

    <x-slot name="subheader">
        {{ __('Match each column in your file to a lead field.') }}
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('leads.import.create') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>{{ __('Choose another file') }}
        </a>
    </x-slot>

    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <div class="toolbar">
        <div class="cell-title">
            <i class="bi bi-filetype-csv me-1 text-secondary"></i>{{ $fileName }}
        </div>

        <span class="pill pill-tag">
            {{ optional($list)->list_name ?: __('List #:id', ['id' => optional($list)->list_id]) }}
        </span>

        <div class="toolbar-spacer"></div>

        <div class="toolbar-count">
            {{ number_format($rowCount) }} {{ Str::plural('row', $rowCount) }} &middot;
            {{ $columns }} {{ Str::plural('column', $columns) }}
        </div>
    </div>

    <form action="{{ route('leads.import.store') }}" method="POST">
        @csrf

        <div class="card card-table mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 22rem;">{{ __('Lead Field') }}</th>
                                <th>{{ __('File Column') }}</th>
                                <th>{{ __('Sample Values') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < $columns; $i++)
                                <tr>
                                    <td>
                                        <select name="mapping[{{ $i }}]" class="form-select">
                                            <option value="">{{ __('— Do not import —') }}</option>
                                            @foreach ($fields as $column => $label)
                                                <option value="{{ $column }}"
                                                        @selected(old("mapping.$i", $guesses[$i] ?? '') === $column)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="cell-title">
                                            {{ $header[$i] ?? __('Column :n', ['n' => $i + 1]) }}
                                        </div>
                                        <div class="cell-id">{{ __('index :n', ['n' => $i]) }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $values = collect($sample)
                                                ->map(fn ($row) => trim((string) ($row[$i] ?? '')))
                                                ->filter()
                                                ->take(3);
                                        @endphp

                                        @if ($values->isNotEmpty())
                                            <span class="cell-mono">{{ $values->implode('  ·  ') }}</span>
                                        @else
                                            <span class="pill-muted">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check2 me-1"></i>{{ __('Import :count leads', ['count' => number_format($rowCount)]) }}
            </button>

            <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>

            <span class="toolbar-count ms-2">
                {{ __('Phone Number must be mapped to one column.') }}
            </span>
        </div>
    </form>
</x-app-layout>
