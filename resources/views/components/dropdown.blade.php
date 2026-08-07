@props(['align' => 'right'])

@php
    $alignmentClass = $align === 'left' ? 'dropdown-menu-start' : 'dropdown-menu-end';
@endphp

<div class="dropdown">
    <div data-bs-toggle="dropdown" aria-expanded="false" role="button">
        {{ $trigger }}
    </div>

    <ul class="dropdown-menu {{ $alignmentClass }}">
        <li>{{ $content }}</li>
    </ul>
</div>
