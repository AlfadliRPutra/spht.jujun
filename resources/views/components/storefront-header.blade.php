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
    <div class="app-container py-2 d-flex align-items-center justify-content-between gap-2">
        <span class="small d-none d-md-inline">
            <i class="ti ti-truck me-1"></i> Gratis ongkir min. Rp 100.000 &middot; Panen segar setiap hari
        </span>
        <span class="small d-inline d-md-none">
            <i class="ti ti-truck me-1"></i> Panen segar setiap hari
        </span>
        <span class="small d-flex align-items-center gap-3">
            @auth
                <span class="d-none d-sm-inline">Halo, <strong>{{ $user->name }}</strong></span>
            @else
                <a href="{{ route('login') }}" class="text-white text-decoration-none">Masuk</a>
                <a href="#" class="text-white text-decoration-none d-none d-sm-inline" data-bs-toggle="modal" data-bs-target="#registerRoleModal">Daftar</a>
            @endauth
        </span>
    </div>
</div>

<nav class="storefront-nav navbar navbar-expand-lg sticky-top">
    <div class="app-container d-flex align-items-center gap-2">
        <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center text-decoration-none m-0 p-0">
            <x-logo :width="32" :height="32" />
            <span class="ms-2 fw-bold text-success d-none d-md-inline">SPHT Jujun</span>
        </a>

        <form action="{{ route('pelanggan.katalog.index') }}" method="GET" class="flex-grow-1 mx-lg-3 order-3 order-lg-2">
            <div class="input-group input-group-flat">
                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search"></i></span>
                <input type="search" name="q" class="form-control border-start-0" placeholder="Cari produk segar..." value="{{ request('q') }}">
            </div>
        </form>

        <ul class="navbar-nav d-none d-lg-flex align-items-center order-2">
            <li class="nav-item dropdown me-2">
                <a href="#" class="nav-link dropdown-toggle px-2" data-bs-toggle="dropdown">
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
        </ul>

        <div class="d-flex align-items-center gap-2 order-lg-3 ms-auto">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm">Masuk</a>
                <a href="#" class="btn btn-success btn-sm d-none d-sm-inline-flex" data-bs-toggle="modal" data-bs-target="#registerRoleModal">Daftar</a>
            @else
                @if ($role === UserRole::Pelanggan)
                    <a href="{{ route('pelanggan.keranjang.index') }}"
                       class="btn btn-success btn-sm position-relative">
                        <i class="ti ti-shopping-cart"></i>
                        <span class="d-none d-md-inline ms-1">Keranjang</span>
                        @if ($cartCount > 0)
                            <span class="badge bg-red position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                        @endif
                    </a>
                @elseif ($role === UserRole::Petani || $role === UserRole::Admin)
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-success btn-sm">
                        <i class="ti ti-layout-dashboard"></i>
                        <span class="d-none d-md-inline ms-1">Dashboard</span>
                    </a>
                @endif

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none text-dark p-1" data-bs-toggle="dropdown">
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
</nav>

<div class="d-lg-none app-container py-2">
    <div class="filter-pills" style="gap:.3rem;overflow-x:auto;display:flex;">
        @foreach ($rootCategories as $root)
            <a href="{{ route('pelanggan.katalog.index', ['category' => $root->slug]) }}"
               class="pill" style="white-space:nowrap;border-radius:999px;padding:.3rem .75rem;font-size:.8rem;border:1px solid var(--spht-border);background:#fff;color:var(--spht-ink);text-decoration:none;">
                @if ($root->icon)<i class="ti ti-{{ $root->icon }} me-1"></i>@endif{{ $root->nama }}
            </a>
        @endforeach
    </div>
</div>
