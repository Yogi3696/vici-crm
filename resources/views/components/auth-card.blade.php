@props(['title' => null, 'subtitle' => null])

<div class="card auth-card">
    <div class="card-body p-4 p-sm-5">
        <div class="text-center mb-4">
            {{ $logo ?? '' }}

            @if ($title)
                <h2 class="auth-title mt-3 mb-1">{{ $title }}</h2>
            @endif

            @if ($subtitle)
                <p class="text-muted small mb-0">{{ $subtitle }}</p>
            @endif
        </div>

        {{ $slot }}
    </div>
</div>
