@props(['title' => null, 'active' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="dns-prefetch" href="//images.unsplash.com">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">

    @include('partials.theme')

    <style>
        .app-container{ width:100%;max-width:1400px;margin-inline:auto;padding-inline:14px; }
        @media (min-width:1200px){ .app-container{ padding-inline:24px; } }

        /* Storefront-specific overrides */
        .storefront-nav{
            background:#fff;border-bottom:1px solid var(--border);
            transition: box-shadow .25s ease;
        }
        .storefront-nav.is-stuck{ box-shadow: 0 8px 22px -12px rgba(15,23,42,.18); }
        .storefront-nav .nav-link.active{ color: var(--brand-700); }

        /* Promo strip di atas navbar */
        .promo-strip{
            background: linear-gradient(90deg, var(--brand-700), var(--brand-500));
            color:#ecfdf5;font-size:.8rem;letter-spacing:.01em;
        }
        .promo-strip a{ color:#fff;text-decoration:underline;text-underline-offset:3px; }

        .category-mega{ min-width:620px;padding:1.1rem 1.25rem;border-radius:var(--radius-lg); box-shadow: var(--shadow-md); }
        .category-mega .category-group-title{ color: var(--brand-700);font-weight:700;font-size:.78rem;letter-spacing:.04em;text-transform:uppercase; }
        .category-mega .category-sub{ color: var(--ink);font-size:.92rem; padding:.35rem .55rem; border-radius:var(--radius-sm); }
        .category-mega .category-sub:hover{ color: var(--brand-700);background: var(--brand-50); }

        /* Footer */
        .site-footer{
            background:linear-gradient(180deg,#fff 0%, #fcfdfe 100%);
            border-top:1px solid var(--border);
            color:var(--muted);
        }
        .site-footer .ft-title{ font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-2);margin-bottom:.85rem; }
        .site-footer a{ color:var(--muted);text-decoration:none; }
        .site-footer a:hover{ color: var(--brand-700); }
        .ft-pay{ background:#fff;border:1px solid var(--border);border-radius:8px;padding:.35rem .6rem;font-size:.7rem;color:var(--muted);font-weight:600; }

        @keyframes sphtFadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        .reveal { opacity: 0; }
        .reveal.is-visible { animation: sphtFadeInUp .5s ease forwards; }
        @media (prefers-reduced-motion: reduce){
            .reveal, .reveal.is-visible { animation: none !important; opacity: 1 !important; transform: none !important; }
        }

        img[loading="lazy"] { background: #f1f3f5; }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    @auth
        @php $promoUser = auth()->user(); @endphp
    @endauth

    {{-- Promo strip atas (informasi pengiriman simulasi) --}}
    <div class="promo-strip py-2 d-none d-md-block">
        <div class="app-container d-flex justify-content-between align-items-center">
            <div><i class="ti ti-truck me-1"></i> Ongkir terjangkau dari petani lokal langsung ke pintu Anda.</div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('pelanggan.katalog.index') }}"><i class="ti ti-leaf me-1"></i>Hasil Tani Segar</a>
                <a href="#"><i class="ti ti-headset me-1"></i>Pusat Bantuan</a>
            </div>
        </div>
    </div>

    <x-storefront-header :active="$active" />

    <main class="py-3 flex-grow-1">
        <div class="app-container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2" role="alert">
                    <i class="ti ti-circle-check"></i><span>{{ session('success') }}</span>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2" role="alert">
                    <i class="ti ti-alert-circle"></i><span>{{ session('error') }}</span>
                    <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    @guest
        <x-register-role-modal />
    @endguest

    <footer class="site-footer pt-5 pb-4 mt-auto">
        <div class="app-container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <x-logo :width="36" :height="36" />
                        <span class="fw-bold text-dark">SPHT-JUJUN</span>
                    </div>
                    <p class="small mb-3" style="max-width:340px">
                        Marketplace hasil tani lokal — menghubungkan petani dengan pelanggan
                        secara langsung, tanpa perantara, untuk produk segar dan harga adil.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-icon btn-sm btn-outline-success" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
                        <a href="#" class="btn btn-icon btn-sm btn-outline-success" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                        <a href="#" class="btn btn-icon btn-sm btn-outline-success" aria-label="WhatsApp"><i class="ti ti-brand-whatsapp"></i></a>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <div class="ft-title">Belanja</div>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        <li><a href="{{ route('pelanggan.katalog.index') }}">Katalog</a></li>
                        <li><a href="{{ route('pelanggan.katalog.index', ['sort' => 'sold_desc']) }}">Paling Laris</a></li>
                        <li><a href="{{ route('pelanggan.katalog.index', ['sort' => 'newest']) }}">Terbaru</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-2">
                    <div class="ft-title">Akun</div>
                    <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                        @auth
                            <li><a href="{{ route('profile.edit') }}">Profil Saya</a></li>
                            @if (auth()->user()->role === \App\Enums\UserRole::Pelanggan)
                                <li><a href="{{ route('pelanggan.pesanan.index') }}">Pesanan Saya</a></li>
                                <li><a href="{{ route('pelanggan.keranjang.index') }}">Keranjang</a></li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}">Masuk</a></li>
                            <li><a href="#" data-bs-toggle="modal" data-bs-target="#registerRoleModal">Daftar</a></li>
                        @endauth
                    </ul>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="ft-title">Pembayaran &amp; Pengiriman</div>
                    <p class="small mb-2">
                        Pembayaran aman dengan VA Bank, QRIS, e-wallet, dan kartu kredit.
                        Ongkir disimulasikan berdasarkan zona kecamatan/kota — tanpa kurir eksternal.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="ft-pay">QRIS</span>
                        <span class="ft-pay">VA Bank</span>
                        <span class="ft-pay">GoPay</span>
                        <span class="ft-pay">OVO</span>
                        <span class="ft-pay">Dana</span>
                        <span class="ft-pay">Credit Card</span>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 small">
                <div>&copy; {{ date('Y') }} {{ config('app.name') }}. Panen segar dari petani lokal.</div>
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item"><a href="#">Tentang</a></li>
                    <li class="list-inline-item"><a href="#">Kebijakan Privasi</a></li>
                    <li class="list-inline-item"><a href="#">Syarat &amp; Ketentuan</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js" defer></script>
    <script>
        (function () {
            // Sticky shadow on scroll
            var nav = document.querySelector('.storefront-nav');
            if (nav) {
                var onScroll = function () {
                    if (window.scrollY > 4) nav.classList.add('is-stuck');
                    else nav.classList.remove('is-stuck');
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            }

            // Reveal-on-scroll animation
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
