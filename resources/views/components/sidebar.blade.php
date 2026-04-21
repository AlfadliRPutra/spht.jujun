@props(['active' => null])

@php
    use App\Enums\UserRole;

    $role = auth()->user()?->role;

    $navItems = match ($role) {
        UserRole::Petani => [
            ['key' => 'dashboard',      'label' => 'Beranda',         'icon' => 'home',      'route' => 'dashboard'],
            ['key' => 'petani.produk',  'label' => 'Produk Saya',     'icon' => 'package',   'route' => 'petani.produk.index'],
            ['key' => 'petani.pesanan', 'label' => 'Pesanan Masuk',   'icon' => 'cart',      'route' => 'petani.pesanan.index'],
            ['key' => 'petani.laporan', 'label' => 'Laporan',         'icon' => 'file-text', 'route' => 'petani.laporan.index'],
        ],
        UserRole::Pelanggan => [
            ['key' => 'dashboard',           'label' => 'Beranda',       'icon' => 'home',      'route' => 'dashboard'],
            ['key' => 'pelanggan.katalog',   'label' => 'Belanja',       'icon' => 'package',   'route' => 'pelanggan.katalog.index'],
            ['key' => 'pelanggan.keranjang', 'label' => 'Keranjang',     'icon' => 'cart',      'route' => 'pelanggan.keranjang.index'],
            ['key' => 'pelanggan.pesanan',   'label' => 'Pesanan Saya',  'icon' => 'file-text', 'route' => 'pelanggan.pesanan.index'],
        ],
        UserRole::Admin => [
            ['key' => 'dashboard',         'label' => 'Dashboard',         'icon' => 'home',     'route' => 'dashboard'],
            ['key' => 'admin.pengguna',    'label' => 'Pengguna',          'icon' => 'users',    'route' => 'admin.pengguna.index'],
            ['key' => 'admin.verifikasi',  'label' => 'Verifikasi Petani', 'icon' => 'shield',   'route' => 'admin.verifikasi.index'],
            ['key' => 'admin.produk',      'label' => 'Produk',            'icon' => 'package',  'route' => 'admin.produk.index'],
            ['key' => 'admin.kategori',    'label' => 'Kategori',          'icon' => 'category', 'route' => 'admin.kategori.index'],
        ],
        default => [
            ['key' => 'dashboard', 'label' => 'Beranda', 'icon' => 'home', 'route' => 'dashboard'],
        ],
    };

    $icons = [
        'home'      => '<path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>',
        'users'     => '<path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>',
        'file-text' => '<path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/>',
        'package'   => '<path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/>',
        'cart'      => '<path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>',
        'shield'    => '<path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/><path d="M9 12l2 2l4 -4"/>',
        'category'  => '<path d="M4 4h6v6h-6z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6h-6z"/><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>',
        'settings'  => '<path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>',
    ];
@endphp

<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <x-logo class="navbar-brand-autodark" />

        <div class="collapse navbar-collapse" id="sidebar-menu">
            <ul class="navbar-nav pt-lg-3">
                @foreach ($navItems as $item)
                    <li @class(['nav-item', 'active' => $active === $item['key']])>
                        <a class="nav-link" href="{{ $item['route'] ? route($item['route']) : '#' }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    {!! $icons[$item['icon']] !!}
                                </svg>
                            </span>
                            <span class="nav-link-title">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</aside>
