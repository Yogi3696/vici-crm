@props(['disabled' => false, 'name' => null])

<input
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge([
        'class' => 'form-control' . ($name && $errors->has($name) ? ' is-invalid' : ''),
    ]) }}
>

@if ($name && $errors->has($name))
    <div class="invalid-feedback">{{ $errors->first($name) }}</div>
@endif
