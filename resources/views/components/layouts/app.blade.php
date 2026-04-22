<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">
    @stack('styles')
</head>
<body>
    <div class="page">
        <x-sidebar :active="$active ?? null" />

        <div class="page-wrapper">
            <x-header :title="$title ?? null" />

            <div class="page-body">
                <div class="container-xl">
                    {{ $slot }}
                </div>
            </div>

            <x-footer />
        </div>
    </div>

    @guest
        <x-register-role-modal />
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    @stack('scripts')
</body>
</html>
