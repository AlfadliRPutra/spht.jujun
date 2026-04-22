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

<nav class="storefront-nav navbar navbar-expand sticky-top">
    <div class="app-container d-flex align-items-center gap-2">
        <a href="{{ route('home') }}" class="navbar-brand m-0 p-0" aria-label="{{ config('app.name') }}">
            <x-logo :width="32" :height="32" />
        </a>

        <a href="{{ route('home') }}"
           class="btn btn-link text-decoration-none px-2 d-none d-md-inline-flex align-items-center {{ $active === 'pelanggan.katalog' ? 'fw-semibold text-success' : 'text-dark' }}">
            <i class="ti ti-building-store me-1"></i> Belanja
        </a>

        <div class="dropdown">
            <button type="button" class="btn btn-link text-dark text-decoration-none px-2" data-bs-toggle="dropdown">
                <i class="ti ti-category"></i>
                <span class="d-none d-md-inline ms-1">Kategori</span>
            </button>
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
        </div>

        <form action="{{ route('pelanggan.katalog.index') }}" method="GET" class="flex-grow-1">
            <div class="input-group input-group-flat">
                <span class="input-group-text bg-white border-end-0"><i class="ti ti-search"></i></span>
                <input type="search" name="q" class="form-control border-start-0" placeholder="Cari produk..." value="{{ request('q') }}">
            </div>
        </form>

        <div class="d-flex align-items-center gap-2 ms-auto">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-success btn-sm d-none d-sm-inline-flex">Daftar</a>
            @else
                @if ($role === UserRole::Pelanggan)
                    <a href="{{ route('pelanggan.keranjang.index') }}"
                       class="btn btn-icon btn-success position-relative" title="Keranjang">
                        <i class="ti ti-shopping-cart"></i>
                        @if ($cartCount > 0)
                            <span class="badge bg-red position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                <div class="dropdown">
                    <button type="button" class="btn btn-link p-1 text-decoration-none d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-label="Menu akun">
                        <span class="avatar avatar-sm" style="background-image:url({{ asset('img/avatars/000m.jpg') }})"></span>
                        <span class="d-none d-md-inline text-dark fw-medium text-truncate" style="max-width:140px">{{ $user->name }}</span>
                        <i class="ti ti-chevron-down d-none d-md-inline text-secondary small"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="text-secondary small">{{ $role?->label() }}</div>
                        </div>

                        @if ($role === UserRole::Pelanggan)
                            <a href="{{ route('pelanggan.keranjang.index') }}" class="dropdown-item"><i class="ti ti-shopping-cart me-2"></i>Keranjang</a>
                            <a href="{{ route('pelanggan.pesanan.index') }}" class="dropdown-item"><i class="ti ti-receipt me-2"></i>Pesanan Saya</a>
                            <div class="dropdown-divider"></div>
                        @elseif ($role === UserRole::Petani || $role === UserRole::Admin)
                            <a href="{{ route('dashboard') }}" class="dropdown-item"><i class="ti ti-layout-dashboard me-2"></i>Dashboard</a>
                            <div class="dropdown-divider"></div>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="ti ti-user me-2"></i>Profil</a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="ti ti-logout me-2"></i>Keluar</button>
                        </form>
                    </div>
                </div>
            @endguest
        </div>
    </div>
</nav>
