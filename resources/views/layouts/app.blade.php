<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'ViciCRM') }}</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100">
    @include('layouts.navigation')

    @isset($header)
        <div class="page-heading py-2">
            <div class="container-fluid px-4">
                <h4 class="mb-0">{{ $header }}</h4>
            </div>
        </div>
    @endisset

    <main class="flex-grow-1 py-3">
        <div class="container-fluid px-4">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    <footer class="app-footer py-2">
        <div class="container-fluid px-4 d-flex justify-content-between flex-wrap gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'ViciCRM') }}</span>
            <span>{{ __('All rights reserved.') }}</span>
        </div>
    </footer>
</body>
</html>
