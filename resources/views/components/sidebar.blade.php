@props(['active' => null])

@php
    use App\Enums\UserRole;
    use App\Models\User;

    $user = auth()->user();
    $role = $user?->role;

    $pendingVerif = $role === UserRole::Admin
        ? User::where('role', UserRole::Petani)
            ->where('is_verified', false)
            ->whereNotNull('verification_submitted_at')
            ->count()
        : 0;

    $pesananMasuk = $role === UserRole::Petani
        ? $user->petaniIncomingOrdersCount()
        : 0;

    // Menu disusun per "section" untuk pengelompokan visual yang lebih bersih.
    $petaniSections = [
        ['label' => 'Utama', 'items' => [
            ['key' => 'dashboard',       'label' => 'Beranda',     'icon' => 'home',     'route' => 'dashboard'],
            ['key' => 'petani.produk',   'label' => 'Produk Saya', 'icon' => 'package',  'route' => 'petani.produk.index'],
        ]],
        ['label' => 'Penjualan', 'items' => [
            ['key' => 'petani.pesanan', 'label' => 'Pesanan Masuk',   'icon' => 'cart',      'route' => 'petani.pesanan.index', 'badge' => $pesananMasuk],
            ['key' => 'petani.laporan', 'label' => 'Riwayat Transaksi','icon' => 'file-text', 'route' => 'petani.laporan.index'],
        ]],
    ];
    if ($role === UserRole::Petani && ! $user->is_verified) {
        $petaniSections[] = ['label' => 'Akun', 'items' => [
            ['key' => 'petani.verifikasi', 'label' => 'Verifikasi Akun', 'icon' => 'shield', 'route' => 'petani.verifikasi.index'],
        ]];
    }

    $adminSections = [
        ['label' => 'Utama', 'items' => [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'route' => 'dashboard'],
        ]],
        ['label' => 'Manajemen', 'items' => [
            ['key' => 'admin.pengguna',   'label' => 'Pengguna',         'icon' => 'users',    'route' => 'admin.pengguna.index'],
            ['key' => 'admin.verifikasi', 'label' => 'Verifikasi Petani','icon' => 'shield',   'route' => 'admin.verifikasi.index', 'badge' => $pendingVerif],
            ['key' => 'admin.toko',       'label' => 'Toko & Produk',    'icon' => 'package',  'route' => 'admin.toko.index'],
            ['key' => 'admin.kategori',   'label' => 'Kategori',         'icon' => 'category', 'route' => 'admin.kategori.index'],
        ]],
        ['label' => 'Operasional', 'items' => [
            ['key' => 'admin.tarif-ongkir', 'label' => 'Tarif Ongkir', 'icon' => 'truck', 'route' => 'admin.tarif-ongkir.index'],
        ]],
        ['label' => 'Konten', 'items' => [
            ['key' => 'admin.hero', 'label' => 'Hero Banner', 'icon' => 'image', 'route' => 'admin.hero.index'],
        ]],
    ];

    $sections = match ($role) {
        UserRole::Petani => $petaniSections,
        UserRole::Admin  => $adminSections,
        default          => [],
    };

    // Inisial untuk avatar fallback.
    $initials = $user
        ? collect(explode(' ', trim($user->name)))->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '';
@endphp

<style>
    .navbar-vertical{
        background: linear-gradient(180deg, var(--side-bg) 0%, var(--side-bg-2) 100%) !important;
        border:0 !important;
        box-shadow: 4px 0 24px -16px rgba(0,0,0,.5);
    }
    .navbar-vertical .navbar-brand{ padding:1.1rem 1.25rem .25rem;display:flex;align-items:center;gap:.6rem; }
    .navbar-vertical .navbar-brand img{ filter: drop-shadow(0 2px 8px rgba(16,185,129,.4)); }
    .navbar-vertical .navbar-brand span{
        font-weight:800;font-size:1.05rem;color:#fff;letter-spacing:-.02em;
        background: linear-gradient(135deg, #fff 0%, #a7f3d0 100%);
        -webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;
    }

    /* Role chip */
    .side-rolechip{
        display:inline-flex;align-items:center;gap:.3rem;
        padding:.25rem .55rem;border-radius:999px;
        font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
        background: rgba(16,185,129,.18);color: #6ee7b7;
        border:1px solid rgba(16,185,129,.3);
    }
    .side-rolechip.is-admin{ background: rgba(99,102,241,.18); color:#a5b4fc; border-color: rgba(99,102,241,.3); }

    /* Sections */
    .side-section{ padding: .25rem .75rem .35rem; }
    .side-section-label{
        font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
        color: var(--side-text-soft);padding:.6rem .9rem .4rem;
    }

    /* Nav items */
    .side-nav{ list-style:none;margin:0;padding:0; }
    .side-nav-item{ margin:2px 0; }
    .side-nav-link{
        display:flex;align-items:center;gap:.7rem;
        padding:.65rem .8rem;border-radius: var(--radius-sm);
        color: var(--side-text);text-decoration:none;
        font-weight:500;font-size:.93rem;letter-spacing:-.005em;
        position:relative;transition: background .15s, color .15s, transform .15s;
    }
    .side-nav-link:hover{ background: var(--side-hover); color:#fff; }
    .side-nav-link.is-active{
        background: var(--side-active);
        color:#fff;font-weight:600;
        box-shadow: inset 0 0 0 1px var(--side-active-bd);
    }
    .side-nav-link.is-active::before{
        content:"";position:absolute;left:-12px;top:50%;transform:translateY(-50%);
        width:4px;height:22px;background: var(--brand-500);border-radius:0 4px 4px 0;
    }
    .side-nav-link .icon{ width:22px;height:22px;flex-shrink:0; }
    .side-nav-link .badge{
        margin-left:auto;background: var(--accent);color:#fff;
        font-size:.65rem;padding:.2em .5em;font-weight:700;
    }

    /* Footer user card */
    .side-foot{
        margin-top:auto;padding:1rem;border-top:1px solid rgba(255,255,255,.06);
    }
    .side-user{
        display:flex;align-items:center;gap:.7rem;
        background: rgba(255,255,255,.04);
        border:1px solid rgba(255,255,255,.06);
        padding:.7rem .8rem;border-radius: var(--radius);
    }
    .side-user .av{
        width:38px;height:38px;border-radius:50%;
        background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
        color:#fff;display:inline-flex;align-items:center;justify-content:center;
        font-weight:700;font-size:.85rem;flex-shrink:0;
    }
    .side-user .name{ color:#fff;font-weight:600;font-size:.9rem;line-height:1.1; }
    .side-user .email{ color: var(--side-text-soft);font-size:.72rem;line-height:1.2; }
    .side-user .logout{
        margin-left:auto;color: var(--side-text-soft);
        background:transparent;border:0;width:32px;height:32px;border-radius:8px;
        display:inline-flex;align-items:center;justify-content:center;
        transition: background .15s, color .15s;
    }
    .side-user .logout:hover{ background: rgba(239,68,68,.15);color:#fca5a5; }

    .navbar-vertical .container-fluid{
        padding:0;
        display:flex;flex-direction:column;height:100%;
    }
    .navbar-vertical .navbar-collapse{
        flex:1 1 auto;display:flex;flex-direction:column;
        padding:.5rem .25rem;overflow-y:auto;
    }
</style>

<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler position-absolute end-0 top-0 m-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a href="{{ route('home') }}" class="navbar-brand m-0">
            <img src="{{ asset('img/logo.png') }}" width="36" height="36" alt="{{ config('app.name') }}" class="navbar-brand-image">
            <span>SPHT-JUJUN</span>
        </a>

        @if ($role)
            <div class="px-3 mb-2">
                <span class="side-rolechip {{ $role === UserRole::Admin ? 'is-admin' : '' }}">
                    <i class="ti ti-{{ $role === UserRole::Admin ? 'shield-lock' : 'plant-2' }}"></i>
                    {{ $role->label() }}
                </span>
            </div>
        @endif

        <div class="collapse navbar-collapse" id="sidebar-menu">
            @foreach ($sections as $section)
                <div class="side-section">
                    <div class="side-section-label">{{ $section['label'] }}</div>
                    <ul class="side-nav">
                        @foreach ($section['items'] as $item)
                            @php $isActive = $active === $item['key']; @endphp
                            <li class="side-nav-item">
                                <a class="side-nav-link {{ $isActive ? 'is-active' : '' }}"
                                   href="{{ $item['route'] ? route($item['route']) : '#' }}">
                                    <span class="icon">
                                        <i class="ti ti-{{ match($item['icon']){
                                            'home' => 'layout-dashboard',
                                            'users' => 'users',
                                            'file-text' => 'file-invoice',
                                            'package' => 'package',
                                            'cart' => 'shopping-cart',
                                            'shield' => 'shield-check',
                                            'category' => 'category-2',
                                            'image' => 'photo',
                                            'truck' => 'truck-delivery',
                                            default => $item['icon'],
                                        } }}"></i>
                                    </span>
                                    <span>{{ $item['label'] }}</span>
                                    @if (! empty($item['badge']) && $item['badge'] > 0)
                                        <span class="badge">{{ $item['badge'] > 99 ? '99+' : $item['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="side-foot">
                <div class="side-user">
                    <span class="av">{{ $initials ?: 'U' }}</span>
                    <div class="flex-fill text-truncate">
                        <div class="name text-truncate">{{ $user->name }}</div>
                        <div class="email text-truncate">{{ $user->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="logout" title="Keluar">
                            <i class="ti ti-logout"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
