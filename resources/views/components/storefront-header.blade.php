@props(['active' => null])

@php
    use App\Enums\UserRole;
    use App\Models\Category;

    $user      = auth()->user();
    $role      = $user?->role;
    $cartCount = $role === UserRole::Pelanggan
        ? (int) ($user->cart?->items()->sum('jumlah') ?? 0)
        : 0;

    $rootCategories = Category::roots()
        ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('nama')])
        ->orderBy('sort_order')
        ->orderBy('nama')
        ->get();
@endphp

<div class="storefront-topbar">
    <div class="container-xl py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <span><i class="ti ti-phone me-1"></i> +62 812 0000 0000</span>
            <span class="d-none d-md-inline">Panen segar setiap hari &middot; Gratis ongkir min. Rp 100.000</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            @auth
                <span class="d-none d-md-inline">Halo, <strong>{{ $user->name }}</strong></span>
            @else
                <a href="{{ route('login') }}" class="text-white text-decoration-none">Masuk</a>
                <a href="{{ route('register') }}" class="text-white text-decoration-none">Daftar</a>
            @endauth
        </div>
    </div>
</div>

<nav class="storefront-nav navbar navbar-expand-lg sticky-top">
    <div class="container-xl">
        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
            <x-logo />
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#storefront-menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="storefront-menu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="ti ti-category me-1"></i> Kategori
                    </a>
                    <div class="dropdown-menu category-mega">
                        <div class="row g-3">
                            @foreach ($rootCategories as $root)
                                <div class="col-6 col-md-4">
                                    <a href="{{ route('pelanggan.katalog.index', ['category' => $root->slug]) }}"
                                       class="category-group-title d-block text-decoration-none mb-2">
                                        @if ($root->icon)<i class="ti ti-{{ $root->icon }} me-1"></i>@endif
                                        {{ $root->nama }}
                                    </a>
                                    @foreach ($root->children as $child)
                                        <a href="{{ route('pelanggan.katalog.index', ['category' => $child->slug]) }}"
                                           class="category-sub d-block py-1 px-2 rounded text-decoration-none">
                                            {{ $child->nama }}
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pelanggan.katalog.index') }}"
                       class="nav-link {{ $active === 'pelanggan.katalog' ? 'active' : '' }}">Katalog</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pelanggan.katalog.index', ['sort' => 'terpopuler']) }}" class="nav-link">Terlaris</a>
                </li>
                @auth
                    @if ($role === UserRole::Pelanggan)
                        <li class="nav-item">
                            <a href="{{ route('pelanggan.pesanan.index') }}"
                               class="nav-link {{ $active === 'pelanggan.pesanan' ? 'active' : '' }}">Pesanan Saya</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <form action="{{ route('pelanggan.katalog.index') }}" method="GET" class="d-flex me-lg-3 my-2 my-lg-0 search-wrap">
                <div class="input-group">
                    <input type="search" name="q" class="form-control" placeholder="Cari produk..." value="{{ request('q') }}">
                    <button class="btn btn-success" type="submit"><i class="ti ti-search"></i></button>
                </div>
            </form>

            <div class="d-flex align-items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-success">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-success d-none d-sm-inline-flex">Daftar</a>
                @else
                    @if ($role === UserRole::Pelanggan)
                        <a href="{{ route('pelanggan.keranjang.index') }}"
                           class="btn btn-success position-relative {{ $active === 'pelanggan.keranjang' ? 'active' : '' }}">
                            <i class="ti ti-shopping-cart me-1"></i> Keranjang
                            @if ($cartCount > 0)
                                <span class="badge bg-red position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                            @endif
                        </a>
                    @elseif ($role === UserRole::Petani || $role === UserRole::Admin)
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-success">
                            <i class="ti ti-layout-dashboard me-1"></i> Dashboard
                        </a>
                    @endif

                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm" style="background-image:url({{ asset('img/avatars/000m.jpg') }})"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <div class="text-secondary small">{{ $role?->label() }}</div>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="ti ti-user me-1"></i> Profil</a>
                            @if ($role === UserRole::Pelanggan)
                                <a href="{{ route('pelanggan.pesanan.index') }}" class="dropdown-item"><i class="ti ti-receipt me-1"></i> Pesanan Saya</a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="ti ti-logout me-1"></i> Keluar</button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>
