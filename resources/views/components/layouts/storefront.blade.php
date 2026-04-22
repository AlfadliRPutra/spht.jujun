@props(['title' => null, 'active' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="dns-prefetch" href="//images.unsplash.com">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">
    <style>
        :root {
            --spht-green:      #15803d;
            --spht-green-dark: #166534;
            --spht-green-soft: #f0fdf4;
            --spht-border:     #e7eaf0;
            --spht-muted:      #6b7280;
            --spht-ink:        #1f2937;
            --spht-radius:     14px;
            --spht-radius-sm:  10px;
            --spht-radius-lg:  20px;
        }
        body { background: #f7f8fa; color: var(--spht-ink); margin: 0; padding: 0; }

        .app-container { width: 100%; max-width: 1400px; margin-inline: auto; padding-inline: 8px; }
        @media (min-width: 1200px) { .app-container { padding-inline: 16px; } }

        .storefront-topbar { background: var(--spht-green-dark); color: #fff; font-size: .82rem; }
        .storefront-nav { background: #fff; border-bottom: 1px solid var(--spht-border); }
        .storefront-nav .nav-link { color: var(--spht-ink); font-weight: 500; }
        .storefront-nav .nav-link.active { color: var(--spht-green); }
        .storefront-nav .search-wrap { min-width: 260px; }
        .storefront-nav .form-control, .storefront-nav .btn { border-radius: var(--spht-radius-sm); }

        .card { border-radius: var(--spht-radius); border-color: var(--spht-border); }
        .alert { border-radius: var(--spht-radius); }
        .breadcrumb-item a { color: var(--spht-muted); text-decoration: none; }
        .breadcrumb-item.active { color: var(--spht-ink); }

        .category-mega { min-width: 560px; padding: 1rem 1.25rem; border-radius: var(--spht-radius); }
        .category-mega .category-group-title { color: var(--spht-green); font-weight: 700; font-size: .78rem; letter-spacing: .04em; text-transform: uppercase; }
        .category-mega .category-sub { color: var(--spht-ink); font-size: .9rem; }
        .category-mega .category-sub:hover { color: var(--spht-green); background: var(--spht-green-soft); }

        @keyframes sphtFadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        .reveal { opacity: 0; }
        .reveal.is-visible { animation: sphtFadeInUp .5s ease forwards; }
        @media (prefers-reduced-motion: reduce) {
            .reveal, .reveal.is-visible { animation: none !important; opacity: 1 !important; transform: none !important; }
        }

        img[loading="lazy"] { background: #f1f3f5; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <x-storefront-header :active="$active" />

    <main class="py-2 flex-grow-1">
        <div class="app-container">
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
        <div class="app-container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-secondary small">&copy; {{ date('Y') }} {{ config('app.name') }}. Panen segar dari petani lokal.</div>
            <ul class="list-inline list-inline-dots mb-0 small">
                <li class="list-inline-item"><a href="#" class="link-secondary">Tentang</a></li>
                <li class="list-inline-item"><a href="#" class="link-secondary">Bantuan</a></li>
                <li class="list-inline-item"><a href="#" class="link-secondary">Kebijakan</a></li>
            </ul>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <script>
        (function () {
            if (!('IntersectionObserver' in window)) {
                document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.05, rootMargin: '0px 0px -40px 0px' });

            function bindReveal() {
                document.querySelectorAll('.reveal:not(.is-visible)').forEach(function (el) { io.observe(el); });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindReveal);
            } else {
                bindReveal();
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
