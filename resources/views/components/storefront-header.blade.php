@props(['active' => null])

@php
    use App\Enums\UserRole;
    use App\Models\Category;

    $user      = auth()->user();
    $role      = $user?->role;
    $cartCount = $role === UserRole::Pelanggan
        ? (int) ($user->cart?->items()->sum('jumlah') ?? 0)
        : 0;

    $rootCategories = Category::with(['subCategories' => fn ($q) => $q->orderBy('sort_order')->orderBy('nama')])
        ->orderBy('sort_order')
        ->orderBy('nama')
        ->get();

    // Inisial untuk avatar fallback
    $initials = $user
        ? collect(explode(' ', trim($user->name)))->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '';
@endphp

<style>
    /* Storefront premium nav */
    .sf-nav-wrap{ position:sticky;top:0;z-index:1030; }
    .sf-search{
        background:#f1f5f9;border:1px solid transparent;border-radius:999px;
        transition: background .2s, border-color .2s, box-shadow .2s;
    }
    .sf-search:focus-within{
        background:#fff;border-color: var(--brand-200);
        box-shadow: 0 0 0 4px rgba(16,185,129,.12);
    }
    .sf-search input{ background:transparent;border:0;box-shadow:none !important; }
    .sf-search input:focus{ background:transparent; }

    .sf-pill{
        display:inline-flex;align-items:center;gap:.4rem;
        padding:.45rem .8rem;border-radius:999px;
        background:#fff;border:1px solid var(--border);
        color:var(--ink-2);font-weight:600;font-size:.875rem;
        transition: background .15s, border-color .15s, color .15s;
    }
    .sf-pill:hover{ background: var(--brand-50);color: var(--brand-700);border-color: var(--brand-200); }
    .sf-pill.is-active{ background: var(--brand-50);color: var(--brand-700);border-color: var(--brand-200); }
    .sf-pill .ti{ font-size:1.05rem; }

    .sf-iconbtn{
        position:relative;width:42px;height:42px;border-radius:999px;
        display:inline-flex;align-items:center;justify-content:center;
        background:#fff;color:var(--ink-2);border:1px solid var(--border);
        transition: background .15s, color .15s, border-color .15s, transform .15s;
    }
    .sf-iconbtn:hover{ background: var(--brand-50);color: var(--brand-700);border-color: var(--brand-200); }
    .sf-iconbtn .ti{ font-size:1.2rem; }
    .sf-iconbtn .badge-dot{
        position:absolute;top:-3px;right:-3px;
        background: var(--accent);color:#fff;font-size:.65rem;
        min-width:18px;height:18px;border-radius:999px;padding:0 5px;
        display:inline-flex;align-items:center;justify-content:center;
        border:2px solid #fff;font-weight:700;
    }

    .sf-avatar{
        width:36px;height:36px;border-radius:50%;
        background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
        color:#fff;font-weight:700;font-size:.85rem;
        display:inline-flex;align-items:center;justify-content:center;
        letter-spacing:.5px;
    }

    .sf-account-menu{ min-width:280px;border:0;box-shadow: var(--shadow-lg);border-radius: var(--radius-lg);padding:.5rem; }
    .sf-account-menu .dropdown-item{ border-radius: var(--radius-sm);padding:.55rem .75rem;font-weight:500; }
    .sf-account-menu .dropdown-item:hover{ background: var(--brand-50);color: var(--brand-700); }
    .sf-account-menu .dropdown-item .ti{ width:18px;text-align:center; }

    .sf-bottom-row{ background:#fff;border-bottom:1px solid var(--border-2); }
    .sf-bottom-row .sf-pill{ font-size:.825rem;padding:.35rem .7rem; }

    /* Mobile search */
    @media (max-width: 767.98px){
        .sf-search-wrap{ width:100%; }
        .sf-bottom-row{ display:none; }
    }
</style>

<div class="sf-nav-wrap">
    <nav class="storefront-nav navbar navbar-expand">
        <div class="app-container d-flex align-items-center gap-3 py-2">
            <a href="{{ route('home') }}" class="navbar-brand m-0 p-0 d-flex align-items-center gap-2" aria-label="{{ config('app.name') }}">
                <x-logo :width="36" :height="36" />
                <span class="fw-bold d-none d-sm-inline" style="color:var(--ink);letter-spacing:-.02em">SPHT-JUJUN</span>
            </a>

            <form action="{{ route('pelanggan.katalog.index') }}" method="GET" class="sf-search-wrap flex-grow-1 mx-2 d-none d-md-block" style="max-width:560px">
                <div class="sf-search d-flex align-items-center gap-1 px-3 py-1">
                    <i class="ti ti-search text-secondary"></i>
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari hasil tani — sayur, buah, rempah..." value="{{ request('q') }}">
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-3">Cari</button>
                </div>
            </form>

            <div class="d-flex align-items-center gap-2 ms-auto">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-success btn-sm">Masuk</a>
                    <a href="#" class="btn btn-success btn-sm d-none d-sm-inline-flex" data-bs-toggle="modal" data-bs-target="#registerRoleModal">
                        <i class="ti ti-user-plus me-1"></i> Daftar
                    </a>
                @else
                    @if ($role === UserRole::Pelanggan)
                        <a href="{{ route('pelanggan.keranjang.index') }}" class="sf-iconbtn" title="Keranjang">
                            <i class="ti ti-shopping-cart"></i>
                            @if ($cartCount > 0)
                                <span class="badge-dot">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                            @endif
                        </a>
                    @endif

                    <div class="dropdown">
                        <button type="button" class="btn p-1 d-flex align-items-center gap-2 border-0 bg-transparent" data-bs-toggle="dropdown" aria-label="Menu akun">
                            <span class="sf-avatar">{{ $initials ?: 'P' }}</span>
                            <span class="d-none d-md-flex flex-column align-items-start lh-1">
                                <span class="fw-semibold text-dark small">{{ Str::limit($user->name, 16) }}</span>
                                <span class="text-secondary" style="font-size:.7rem">{{ $role?->label() }}</span>
                            </span>
                            <i class="ti ti-chevron-down d-none d-md-inline text-secondary small"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end sf-account-menu">
                            <div class="px-3 py-2 mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="sf-avatar" style="width:42px;height:42px;font-size:1rem">{{ $initials ?: 'P' }}</span>
                                    <div class="lh-sm">
                                        <div class="fw-semibold">{{ $user->name }}</div>
                                        <div class="text-secondary small">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>

                            @if ($role === UserRole::Pelanggan)
                                <a href="{{ route('pelanggan.keranjang.index') }}" class="dropdown-item">
                                    <i class="ti ti-shopping-cart me-2"></i>Keranjang
                                    @if ($cartCount > 0)
                                        <span class="badge bg-success ms-auto">{{ $cartCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('pelanggan.pesanan.index') }}" class="dropdown-item">
                                    <i class="ti ti-receipt me-2"></i>Pesanan Saya
                                </a>
                            @elseif ($role === UserRole::Petani || $role === UserRole::Admin)
                                <a href="{{ route('dashboard') }}" class="dropdown-item">
                                    <i class="ti ti-layout-dashboard me-2"></i>Dashboard
                                </a>
                            @endif

                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="ti ti-user-cog me-2"></i>Pengaturan Profil
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="ti ti-headset me-2"></i>Bantuan
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ti ti-logout me-2"></i>Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>

        {{-- Mobile search --}}
        <div class="d-md-none w-100 px-3 pb-2">
            <form action="{{ route('pelanggan.katalog.index') }}" method="GET">
                <div class="sf-search d-flex align-items-center gap-1 px-3 py-1">
                    <i class="ti ti-search text-secondary"></i>
                    <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari hasil tani..." value="{{ request('q') }}">
                </div>
            </form>
        </div>
    </nav>

    {{-- Bottom row: shortcuts & categories --}}
    <div class="sf-bottom-row">
        <div class="app-container d-flex align-items-center gap-2 py-2 flex-wrap">
            <a href="{{ route('home') }}" class="sf-pill {{ $active === 'pelanggan.katalog' ? 'is-active' : '' }}">
                <i class="ti ti-home"></i> Beranda
            </a>

            <div class="dropdown">
                <button type="button" class="sf-pill border-0" data-bs-toggle="dropdown">
                    <i class="ti ti-category"></i> Semua Kategori <i class="ti ti-chevron-down small"></i>
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
                                @foreach ($root->subCategories as $child)
                                    <a href="{{ route('pelanggan.katalog.index', ['category' => $root->slug, 'sub' => $child->slug]) }}"
                                       class="category-sub d-block">{{ $child->nama }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ route('pelanggan.katalog.index', ['sort' => 'sold_desc']) }}" class="sf-pill">
                <i class="ti ti-flame"></i> Paling Laris
            </a>
            <a href="{{ route('pelanggan.katalog.index', ['sort' => 'newest']) }}" class="sf-pill">
                <i class="ti ti-sparkles"></i> Terbaru
            </a>
            <a href="{{ route('pelanggan.katalog.index', ['sort' => 'price_asc']) }}" class="sf-pill">
                <i class="ti ti-tag"></i> Harga Terbaik
            </a>

            @auth
                @if ($role === UserRole::Pelanggan)
                    <a href="{{ route('pelanggan.pesanan.index') }}" class="sf-pill ms-auto">
                        <i class="ti ti-package"></i> Pesanan Saya
                    </a>
                @endif
            @endauth
        </div>
    </div>
</div>
