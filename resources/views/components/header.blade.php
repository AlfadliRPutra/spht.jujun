@props(['title' => null])

@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user?->role;

    $cartCount = $role === UserRole::Pelanggan
        ? (int) ($user->cart?->items()->sum('jumlah') ?? 0)
        : 0;

    $pendingVerif = $role === UserRole::Admin
        ? \App\Models\User::where('role', UserRole::Petani)
            ->where('is_verified', false)
            ->whereNotNull('verification_submitted_at')
            ->count()
        : 0;

    $pesananMasuk = $role === UserRole::Petani
        ? $user->petaniIncomingOrdersCount()
        : 0;

    $initials = $user
        ? collect(explode(' ', trim($user->name)))->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '';

    // Greeting waktu lokal — sentuhan ramah di header.
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<style>
    .app-header{
        background:#fff;border-bottom:1px solid var(--border);
        padding:.85rem 0;
    }
    .app-header .container-xl{ display:flex;align-items:center;gap:1rem; }

    .app-title{ font-size:1.25rem;font-weight:700;color:var(--ink);line-height:1.1;margin:0; }
    .app-greet{ color: var(--muted);font-size:.78rem;margin-top:.1rem; }

    .head-iconbtn{
        position:relative;width:40px;height:40px;border-radius:var(--radius-sm);
        display:inline-flex;align-items:center;justify-content:center;
        background:#fff;color:var(--ink-2);border:1px solid var(--border);
        transition: background .15s, color .15s, border-color .15s;
    }
    .head-iconbtn:hover{ background: var(--brand-50);color:var(--brand-700);border-color:var(--brand-200); }
    .head-iconbtn .ti{ font-size:1.2rem; }
    .head-iconbtn .pulse{
        position:absolute;top:6px;right:6px;width:8px;height:8px;border-radius:50%;
        background: var(--accent);
        box-shadow:0 0 0 0 rgba(245,158,11,.6);
        animation: head-pulse 1.8s infinite;
    }
    @keyframes head-pulse{
        0%{ box-shadow:0 0 0 0 rgba(245,158,11,.5);}
        70%{ box-shadow:0 0 0 8px rgba(245,158,11,0);}
        100%{ box-shadow:0 0 0 0 rgba(245,158,11,0);}
    }

    .head-account{
        display:flex;align-items:center;gap:.6rem;padding:.3rem .55rem;
        border-radius: var(--radius-sm);border:1px solid var(--border);
        background:#fff;cursor:pointer;
        transition: background .15s, border-color .15s;
    }
    .head-account:hover{ background: var(--brand-50);border-color: var(--brand-200); }
    .head-account .av{
        width:34px;height:34px;border-radius:50%;
        background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
        color:#fff;display:inline-flex;align-items:center;justify-content:center;
        font-weight:700;font-size:.85rem;
    }
    .head-account .meta .nm{ font-weight:600;color:var(--ink);font-size:.85rem;line-height:1.1; }
    .head-account .meta .rl{ color:var(--muted);font-size:.7rem;line-height:1.2; }

    .head-menu{ min-width:280px;border:0;box-shadow: var(--shadow-lg);border-radius: var(--radius-lg);padding:.5rem; }
    .head-menu .dropdown-item{ border-radius: var(--radius-sm);padding:.55rem .75rem;font-weight:500; }
    .head-menu .dropdown-item:hover{ background: var(--brand-50);color: var(--brand-700); }

    .head-cta{
        background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
        border:0;color:#fff;font-weight:600;
        box-shadow: 0 8px 18px -10px rgba(16,185,129,.6);
    }
    .head-cta:hover{ filter:brightness(1.05);color:#fff; }

    @media (max-width: 575.98px){
        .app-greet{ display:none; }
    }
</style>

<header class="app-header navbar navbar-expand-md d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="flex-fill min-w-0">
            @if ($title)
                <h1 class="app-title text-truncate">{{ $title }}</h1>
                <div class="app-greet">{{ $greeting }}, {{ explode(' ', $user?->name ?? '')[0] ?? '' }} 👋</div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            @auth
                {{-- Quick CTA per role --}}
                @switch($role)
                    @case(UserRole::Petani)
                        <a href="{{ route('petani.produk.create') }}" class="btn head-cta d-none d-sm-inline-flex">
                            <i class="ti ti-plus me-1"></i> Tambah Produk
                        </a>
                        @break

                    @case(UserRole::Pelanggan)
                        <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-outline-success d-none d-sm-inline-flex">
                            <i class="ti ti-building-store me-1"></i> Katalog
                        </a>
                        <a href="{{ route('pelanggan.keranjang.index') }}" class="head-iconbtn" title="Keranjang">
                            <i class="ti ti-shopping-cart"></i>
                            @if ($cartCount > 0)
                                <span class="pulse"></span>
                            @endif
                        </a>
                        @break

                    @case(UserRole::Admin)
                        <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-outline-success d-none d-sm-inline-flex position-relative">
                            <i class="ti ti-shield-check me-1"></i> Verifikasi
                            @if ($pendingVerif > 0)
                                <span class="badge bg-danger ms-2">{{ $pendingVerif }}</span>
                            @endif
                        </a>
                        @break
                @endswitch

                @php
                    $hasNotif = ($role === UserRole::Admin && $pendingVerif > 0)
                             || ($role === UserRole::Petani && $pesananMasuk > 0);
                @endphp
                <div class="dropdown">
                    <button type="button" class="head-iconbtn" data-bs-toggle="dropdown" aria-label="Notifikasi">
                        <i class="ti ti-bell"></i>
                        @if ($hasNotif)
                            <span class="pulse"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end head-menu">
                        <div class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-semibold">Notifikasi</div>
                            <div class="text-secondary small">Aktivitas terkini akun Anda</div>
                        </div>
                        @if ($role === UserRole::Admin && $pendingVerif > 0)
                            <a href="{{ route('admin.verifikasi.index') }}" class="dropdown-item d-flex align-items-start gap-2">
                                <i class="ti ti-shield-check text-warning mt-1"></i>
                                <div>
                                    <div class="fw-semibold">{{ $pendingVerif }} petani menunggu verifikasi</div>
                                    <div class="small text-secondary">Tinjau & putuskan persetujuan akun</div>
                                </div>
                            </a>
                        @elseif ($role === UserRole::Petani && $pesananMasuk > 0)
                            <a href="{{ route('petani.pesanan.index', ['status' => 'dibayar']) }}" class="dropdown-item d-flex align-items-start gap-2">
                                <i class="ti ti-shopping-bag text-success mt-1"></i>
                                <div>
                                    <div class="fw-semibold">{{ $pesananMasuk }} pesanan menunggu dikirim</div>
                                    <div class="small text-secondary">Pelanggan sudah membayar — segera siapkan & kirim pesanan</div>
                                </div>
                            </a>
                        @else
                            <div class="text-center text-secondary small py-3">
                                <i class="ti ti-bell-off d-block mb-1" style="font-size:1.25rem"></i>
                                Belum ada notifikasi baru.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="dropdown">
                    <button type="button" class="head-account" data-bs-toggle="dropdown" aria-label="Menu akun">
                        <span class="av">{{ $initials ?: 'U' }}</span>
                        <span class="meta d-none d-md-flex flex-column align-items-start">
                            <span class="nm">{{ Str::limit($user->name, 14) }}</span>
                            <span class="rl">{{ $role?->label() ?? 'Pengguna' }}</span>
                        </span>
                        <i class="ti ti-chevron-down d-none d-md-inline text-secondary small"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end head-menu">
                        <div class="px-3 py-2 mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="av d-inline-flex align-items-center justify-content-center" style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--brand-500),var(--brand-700));color:#fff;font-weight:700;">{{ $initials ?: 'U' }}</span>
                                <div class="lh-sm">
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="text-secondary small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>

                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="ti ti-user-cog me-2"></i>Pengaturan Profil
                        </a>
                        @if ($role === UserRole::Pelanggan)
                            <a href="{{ route('pelanggan.pesanan.index') }}" class="dropdown-item">
                                <i class="ti ti-receipt me-2"></i>Pesanan Saya
                            </a>
                        @elseif ($role === UserRole::Petani)
                            <a href="{{ route('petani.pesanan.index') }}" class="dropdown-item">
                                <i class="ti ti-shopping-bag me-2"></i>Pesanan Masuk
                            </a>
                            <a href="{{ route('petani.laporan.index') }}" class="dropdown-item">
                                <i class="ti ti-chart-bar me-2"></i>Riwayat Transaksi
                            </a>
                        @endif

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
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-success">Masuk</a>
                <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registerRoleModal">Daftar</a>
            @endauth
        </div>
    </div>
</header>
