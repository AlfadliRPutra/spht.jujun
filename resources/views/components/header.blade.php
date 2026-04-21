@props(['title' => null])

@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user?->role;

    $cartCount = $role === UserRole::Pelanggan
        ? (int) ($user->cart?->items()->sum('jumlah') ?? 0)
        : 0;

    $pendingVerif = $role === UserRole::Admin
        ? \App\Models\User::where('role', UserRole::Petani)->where('is_verified', false)->count()
        : 0;
@endphp

<header class="navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item d-none d-md-flex me-3">
                <div class="btn-list">
                    @switch($role)
                        @case(UserRole::Petani)
                            <a href="{{ route('petani.produk.create') }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 5l0 14"/><path d="M5 12l14 0"/>
                                </svg>
                                Tambah Produk
                            </a>
                            @break

                        @case(UserRole::Pelanggan)
                            <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-outline-primary">Katalog</a>
                            <a href="{{ route('pelanggan.keranjang.index') }}" class="btn btn-primary position-relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                    <path d="M17 17h-11v-14h-2"/>
                                    <path d="M6 5l14 1l-1 7h-13"/>
                                </svg>
                                Keranjang
                                @if ($cartCount > 0)
                                    <span class="badge bg-red ms-1">{{ $cartCount }}</span>
                                @endif
                            </a>
                            @break

                        @case(UserRole::Admin)
                            <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-outline-primary position-relative">
                                Verifikasi Petani
                                @if ($pendingVerif > 0)
                                    <span class="badge bg-red ms-1">{{ $pendingVerif }}</span>
                                @endif
                            </a>
                            @break
                    @endswitch
                </div>
            </div>

            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 p-0 px-2" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <span class="avatar avatar-sm" style="background-image: url({{ asset('img/avatars/000m.jpg') }})"></span>
                    <div class="d-none d-xl-block ps-2">
                        <div>{{ $user?->name ?? 'Tamu' }}</div>
                        <div class="mt-1 small text-secondary">{{ $role?->label() ?? 'Pengguna' }}</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">Profil</a>
                    @if ($role === UserRole::Pelanggan)
                        <a href="{{ route('pelanggan.pesanan.index') }}" class="dropdown-item">Pesanan Saya</a>
                    @elseif ($role === UserRole::Petani)
                        <a href="{{ route('petani.pesanan.index') }}" class="dropdown-item">Pesanan Masuk</a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Keluar</button>
                    </form>
                </div>
            </div>
        </div>

        @if ($title)
            <h2 class="page-title d-none d-md-block">{{ $title }}</h2>
        @endif
    </div>
</header>
