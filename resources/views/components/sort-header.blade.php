@props(['column', 'label', 'sort', 'direction', 'align' => null])

@php
    $active = $sort === $column;
    // Clicking the active column flips it; a new column starts descending.
    $next = $active && $direction === 'desc' ? 'asc' : 'desc';
    $icon = $active
        ? ($direction === 'asc' ? 'bi-sort-up-alt' : 'bi-sort-down')
        : 'bi-arrow-down-up';
@endphp

<th @if($align === 'end') class="text-end" @endif>
    <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $next, 'page' => null]) }}"
       @class(['sort-link', 'is-active' => $active])
       title="{{ __('Sort by :label', ['label' => $label]) }}">
        <span>{{ $label }}</span>
        <i class="bi {{ $icon }} sort-icon"></i>
    </a>
</th>
