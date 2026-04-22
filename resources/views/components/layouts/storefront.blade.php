@props(['title' => null, 'active' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">
    <style>
        body { background: #f6f8fa; }
        .storefront-topbar { background: #0b5d2b; color: #fff; font-size: .85rem; }
        .storefront-nav { background: #fff; box-shadow: 0 1px 0 rgba(0,0,0,.06); }
        .storefront-nav .nav-link { color: #24344d; font-weight: 500; }
        .storefront-nav .nav-link.active { color: #0b5d2b; }
        .storefront-nav .search-wrap { min-width: 260px; }
        .category-mega { min-width: 560px; padding: 1rem 1.25rem; }
        .category-mega .category-group-title { color: #0b5d2b; font-weight: 700; font-size: .8rem; letter-spacing: .04em; text-transform: uppercase; }
        .category-mega .category-sub { color: #24344d; font-size: .9rem; }
        .category-mega .category-sub:hover { color: #0b5d2b; background: #f0fdf4; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <x-storefront-header :active="$active" />

    <main class="py-4 flex-grow-1">
        <div class="container-xl">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    {{ session('error') }}
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    <footer class="py-4 border-top bg-white mt-auto">
        <div class="container-xl d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-secondary small">&copy; {{ date('Y') }} {{ config('app.name') }}. Panen segar dari petani lokal.</div>
            <ul class="list-inline list-inline-dots mb-0 small">
                <li class="list-inline-item"><a href="#" class="link-secondary">Tentang</a></li>
                <li class="list-inline-item"><a href="#" class="link-secondary">Bantuan</a></li>
                <li class="list-inline-item"><a href="#" class="link-secondary">Kebijakan</a></li>
            </ul>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    @stack('scripts')
</body>
</html>
