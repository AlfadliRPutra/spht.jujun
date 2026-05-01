@php
    use App\Enums\OrderStatus;
    use App\Enums\UserRole;
    use App\Models\Category;
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\User;

    // === Statistik utama ===
    $totalUsers       = User::count();
    $totalPetani      = User::where('role', UserRole::Petani)->count();
    $totalPelanggan   = User::where('role', UserRole::Pelanggan)->count();
    $pendingVerif     = User::where('role', UserRole::Petani)
        ->where('is_verified', false)
        ->whereNotNull('verification_submitted_at')
        ->count();
    $totalProduk      = Product::count();
    $totalKategori    = Category::count();

    // Pesanan bulan ini & pendapatan dari order Selesai
    $startOfMonth     = now()->startOfMonth();
    $orderBulanIni    = Order::where('created_at', '>=', $startOfMonth)->count();
    $pendapatanBulanIni = (float) Order::where('status', OrderStatus::Selesai)
        ->where('created_at', '>=', $startOfMonth)
        ->sum('total_harga');

    // Tren pesanan 7 hari terakhir untuk mini-chart bar
    $last7Days = collect(range(6, 0))->map(function ($d) {
        $date = now()->subDays($d)->startOfDay();
        return [
            'label' => $date->translatedFormat('D'),
            'date'  => $date->format('d M'),
            'count' => Order::whereDate('created_at', $date)->count(),
        ];
    });
    $maxDaily = max($last7Days->max('count'), 1);

    // Distribusi status order (untuk visual donut sederhana)
    $statusDist = Order::selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status')
        ->all();
    $totalAllOrders = array_sum($statusDist);

    // Daftar entitas yang butuh perhatian
    $pendingPetani = User::where('role', UserRole::Petani)
        ->where('is_verified', false)
        ->whereNotNull('verification_submitted_at')
        ->latest('verification_submitted_at')
        ->limit(5)
        ->get();

    $latestOrders = Order::with('user')
        ->latest()
        ->limit(6)
        ->get();

    $topProducts = Product::with('petani')
        ->orderByDesc('sold_count')
        ->limit(5)
        ->get();

    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
@endphp

<style>
    /* === Hero === */
    .ad-hero {
        background: linear-gradient(135deg, #312e81 0%, #6366f1 50%, #8b5cf6 100%);
        color:#fff; border-radius:16px; padding:24px 28px; margin-bottom:18px;
        position:relative; overflow:hidden;
    }
    .ad-hero::before {
        content:""; position:absolute; right:-40px; top:-40px; width:200px; height:200px;
        background: radial-gradient(circle, rgba(255,255,255,.18), transparent 70%); border-radius:50%;
    }
    .ad-hero::after {
        content:""; position:absolute; left:-30px; bottom:-50px; width:160px; height:160px;
        background: radial-gradient(circle, rgba(167,139,250,.4), transparent 70%); border-radius:50%;
    }
    .ad-hero h1 { font-size:1.6rem; font-weight:800; letter-spacing:-.02em; margin:0; }
    .ad-hero .greet { font-size:.85rem; opacity:.85; margin-bottom:4px; }
    .ad-hero .desc  { font-size:.92rem; opacity:.92; margin-top:8px; max-width:560px; }
    .ad-hero .date {
        background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
        padding:10px 16px; border-radius:12px; font-weight:600; font-size:.88rem;
        display:inline-flex; align-items:center; gap:8px;
    }

    /* === Stat cards === */
    .ad-stat {
        background:#fff; border:1px solid #e2e8f0; border-radius:14px;
        padding:18px 20px; height:100%;
        transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    .ad-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -10px rgba(15,23,42,.12); }
    .ad-stat .icobg {
        width:48px; height:48px; border-radius:12px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:1.4rem; margin-bottom:10px;
    }
    .ad-stat .icobg.b1 { background:#eef2ff; color:#4f46e5; }
    .ad-stat .icobg.b2 { background:#fef3c7; color:#b45309; }
    .ad-stat .icobg.b3 { background:#dcfce7; color:#15803d; }
    .ad-stat .icobg.b4 { background:#dbeafe; color:#1d4ed8; }
    .ad-stat .lbl { font-size:.78rem; color:#64748b; font-weight:600; }
    .ad-stat .val { font-size:1.85rem; font-weight:800; color:#0f172a; line-height:1.1; margin-top:2px; }
    .ad-stat .sub { font-size:.74rem; color:#94a3b8; margin-top:4px; }
    .ad-stat .sub strong { color:#0f172a; font-weight:700; }
    .ad-stat.is-warn { border-color:#fbbf24; background: linear-gradient(180deg,#fffbeb 0%,#fff 60%); }

    /* === Quick actions grid === */
    .ad-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; margin-bottom:18px; }
    .ad-action {
        background:#fff; border:1px solid #e2e8f0; border-radius:12px;
        padding:14px; text-decoration:none; color:#0f172a;
        display:flex; align-items:center; gap:10px;
        transition: all .15s ease;
    }
    .ad-action:hover { border-color:#6366f1; background:#eef2ff; transform: translateY(-2px); color:#0f172a; }
    .ad-action .ico { width:36px; height:36px; border-radius:10px; background:#eef2ff; color:#4f46e5; display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    .ad-action .name { font-weight:600; font-size:.85rem; line-height:1.2; }
    .ad-action .desc { font-size:.72rem; color:#94a3b8; margin-top:1px; }
    .ad-action.urgent { border-color:#fbbf24; background:#fffbeb; }
    .ad-action.urgent .ico { background:#fef3c7; color:#b45309; }
    .ad-action.urgent .badge {
        margin-left:auto; background:#dc2626; color:#fff; font-weight:700;
        padding:3px 8px; border-radius:999px; font-size:.7rem;
    }

    /* === Section card (panels) === */
    .ad-panel {
        background:#fff; border:1px solid #e2e8f0; border-radius:14px;
        overflow:hidden; height:100%;
    }
    .ad-panel-head {
        padding:14px 18px; border-bottom:1px solid #f1f5f9;
        display:flex; justify-content:space-between; align-items:center; gap:10px;
        background: linear-gradient(180deg,#fbfcff 0%,#fff 100%);
    }
    .ad-panel-head h3 { font-size:.95rem; font-weight:700; margin:0; color:#0f172a; }
    .ad-panel-head .ico { color:#6366f1; }

    /* === 7-day bar chart === */
    .ad-chart-wrap { padding:16px 18px; }
    .ad-bar-row {
        display:grid; grid-template-columns: repeat(7, 1fr); gap:8px;
        align-items:end; height:140px;
    }
    .ad-bar {
        background: linear-gradient(180deg,#a5b4fc,#6366f1);
        border-radius:6px 6px 0 0;
        display:flex; align-items:flex-start; justify-content:center;
        padding-top:6px; color:#fff; font-size:.7rem; font-weight:700;
        min-height:6px;
        transition: filter .15s;
    }
    .ad-bar:hover { filter: brightness(1.1); }
    .ad-bar-label { display:grid; grid-template-columns: repeat(7, 1fr); gap:8px; margin-top:6px; text-align:center; }
    .ad-bar-label > div .day { font-weight:700; font-size:.72rem; color:#475569; }
    .ad-bar-label > div .date { font-size:.68rem; color:#94a3b8; }

    /* === Status donut (pure CSS conic-gradient) === */
    .ad-donut {
        --p-pending: 0deg; --p-dibayar: 0deg; --p-dikirim: 0deg; --p-selesai: 0deg; --p-batal: 0deg;
        width:140px; height:140px; border-radius:50%; flex-shrink:0;
        background: conic-gradient(
            #f59e0b 0 var(--p-pending),
            #3b82f6 var(--p-pending) calc(var(--p-pending) + var(--p-dibayar)),
            #06b6d4 calc(var(--p-pending) + var(--p-dibayar)) calc(var(--p-pending) + var(--p-dibayar) + var(--p-dikirim)),
            #16a34a calc(var(--p-pending) + var(--p-dibayar) + var(--p-dikirim)) calc(var(--p-pending) + var(--p-dibayar) + var(--p-dikirim) + var(--p-selesai)),
            #ef4444 calc(var(--p-pending) + var(--p-dibayar) + var(--p-dikirim) + var(--p-selesai)) 360deg
        );
        position:relative;
    }
    .ad-donut::after {
        content:""; position:absolute; inset:18%; background:#fff; border-radius:50%;
    }
    .ad-donut-label { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; z-index:1; }
    .ad-donut-label .num { font-size:1.4rem; font-weight:800; color:#0f172a; line-height:1; }
    .ad-donut-label .lbl { font-size:.68rem; color:#94a3b8; font-weight:600; margin-top:2px; }
    .ad-legend { display:flex; flex-direction:column; gap:8px; flex:1; }
    .ad-legend-item { display:flex; align-items:center; gap:8px; font-size:.82rem; color:#475569; }
    .ad-legend-item .swatch { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
    .ad-legend-item .count { margin-left:auto; font-weight:700; color:#0f172a; }

    /* === List item === */
    .ad-list-item {
        padding:12px 18px;
        display:flex; align-items:center; gap:12px;
        border-top:1px solid #f1f5f9;
        transition: background .12s;
    }
    .ad-list-item:hover { background:#f8fafc; }
    .ad-list-item .ava {
        width:38px; height:38px; border-radius:50%;
        background: linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;
        display:inline-flex; align-items:center; justify-content:center; font-weight:700;
        flex-shrink:0; font-size:.85rem;
    }
    .ad-list-item .thumb { width:42px; height:42px; border-radius:8px; object-fit:cover; flex-shrink:0; background:#f6f8fa; border:1px solid #e5e7eb; }
    .ad-list-item .name { font-weight:600; color:#0f172a; font-size:.88rem; }
    .ad-list-item .meta { font-size:.74rem; color:#94a3b8; margin-top:2px; }
    .ad-list-item .right { margin-left:auto; text-align:right; flex-shrink:0; }

    .ad-empty {
        padding:36px 16px; text-align:center; color:#94a3b8; font-size:.85rem;
    }
    .ad-empty .ico { font-size:2.2rem; color:#cbd5e1; margin-bottom:8px; display:block; }

    /* === Order rank prefix === */
    .ad-rank {
        width:24px; height:24px; border-radius:50%;
        background:#fef3c7; color:#b45309; font-weight:800; font-size:.75rem;
        display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .ad-rank.r1 { background: linear-gradient(135deg,#fbbf24,#f59e0b); color:#fff; }
    .ad-rank.r2 { background: linear-gradient(135deg,#cbd5e1,#94a3b8); color:#fff; }
    .ad-rank.r3 { background: linear-gradient(135deg,#fdba74,#f97316); color:#fff; }
</style>

{{-- HERO --}}
<div class="ad-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div style="position:relative;z-index:1;">
        <div class="greet">{{ $greeting }}, Admin 👋</div>
        <h1>Panel Admin SPHT-JUJUN</h1>
        <p class="desc mb-0">Pantau aktivitas marketplace, kelola verifikasi, pengguna, dan konten dari satu tempat.</p>
    </div>
    <div class="date" style="position:relative;z-index:1;">
        <i class="ti ti-calendar"></i>
        {{ now()->translatedFormat('l, d F Y') }}
    </div>
</div>

{{-- STAT CARDS --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="ad-stat">
            <div class="icobg b1"><i class="ti ti-users"></i></div>
            <div class="lbl">Total Pengguna</div>
            <div class="val">{{ number_format($totalUsers) }}</div>
            <div class="sub"><strong>{{ $totalPetani }}</strong> petani · <strong>{{ $totalPelanggan }}</strong> pelanggan</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="ad-stat {{ $pendingVerif > 0 ? 'is-warn' : '' }}">
            <div class="icobg b2"><i class="ti ti-shield-check"></i></div>
            <div class="lbl">Verifikasi Pending</div>
            <div class="val">{{ number_format($pendingVerif) }}</div>
            <div class="sub">@if ($pendingVerif > 0) <strong class="text-warning">Perlu ditinjau</strong> @else aman, semua sudah ditinjau @endif</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="ad-stat">
            <div class="icobg b3"><i class="ti ti-shopping-bag"></i></div>
            <div class="lbl">Pesanan Bulan Ini</div>
            <div class="val">{{ number_format($orderBulanIni) }}</div>
            <div class="sub">sejak <strong>{{ $startOfMonth->translatedFormat('d M') }}</strong></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="ad-stat">
            <div class="icobg b4"><i class="ti ti-cash-banknote"></i></div>
            <div class="lbl">Pendapatan Bulan Ini</div>
            <div class="val">Rp&nbsp;{{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
            <div class="sub">dari pesanan <strong>selesai</strong></div>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="ad-actions">
    <a href="{{ route('admin.verifikasi.index') }}" class="ad-action {{ $pendingVerif > 0 ? 'urgent' : '' }}">
        <span class="ico"><i class="ti ti-shield-check"></i></span>
        <div>
            <div class="name">Verifikasi Petani</div>
            <div class="desc">Tinjau pengajuan baru</div>
        </div>
        @if ($pendingVerif > 0)
            <span class="badge">{{ $pendingVerif }}</span>
        @endif
    </a>
    <a href="{{ route('admin.pengguna.index') }}" class="ad-action">
        <span class="ico"><i class="ti ti-users"></i></span>
        <div>
            <div class="name">Pengguna</div>
            <div class="desc">Kelola akun</div>
        </div>
    </a>
    <a href="{{ route('admin.toko.index') }}" class="ad-action">
        <span class="ico"><i class="ti ti-building-store"></i></span>
        <div>
            <div class="name">Toko & Produk</div>
            <div class="desc">{{ $totalProduk }} produk</div>
        </div>
    </a>
    <a href="{{ route('admin.kategori.index') }}" class="ad-action">
        <span class="ico"><i class="ti ti-category-2"></i></span>
        <div>
            <div class="name">Kategori</div>
            <div class="desc">{{ $totalKategori }} kategori</div>
        </div>
    </a>
    <a href="{{ route('admin.hero.index') }}" class="ad-action">
        <span class="ico"><i class="ti ti-photo"></i></span>
        <div>
            <div class="name">Hero Banner</div>
            <div class="desc">Konten halaman utama</div>
        </div>
    </a>
</div>

<div class="row g-3 mb-3">
    {{-- TREN 7 HARI --}}
    <div class="col-lg-7">
        <div class="ad-panel">
            <div class="ad-panel-head">
                <h3><i class="ti ti-chart-bar me-1 ico"></i> Tren Pesanan 7 Hari</h3>
                <span class="text-secondary small">Total {{ $last7Days->sum('count') }} pesanan</span>
            </div>
            <div class="ad-chart-wrap">
                <div class="ad-bar-row">
                    @foreach ($last7Days as $d)
                        @php $h = max(6, ($d['count'] / $maxDaily) * 130); @endphp
                        <div class="ad-bar" style="height: {{ $h }}px" title="{{ $d['date'] }} — {{ $d['count'] }} pesanan">
                            @if ($d['count'] > 0) {{ $d['count'] }} @endif
                        </div>
                    @endforeach
                </div>
                <div class="ad-bar-label">
                    @foreach ($last7Days as $d)
                        <div>
                            <div class="day">{{ $d['label'] }}</div>
                            <div class="date">{{ $d['date'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- DONUT STATUS --}}
    <div class="col-lg-5">
        <div class="ad-panel">
            <div class="ad-panel-head">
                <h3><i class="ti ti-chart-donut me-1 ico"></i> Distribusi Status</h3>
                <span class="text-secondary small">{{ $totalAllOrders }} total</span>
            </div>
            <div class="d-flex align-items-center gap-3 p-3">
                @php
                    $deg = fn ($n) => $totalAllOrders > 0 ? ($n / $totalAllOrders) * 360 : 0;
                    $pPending = $deg($statusDist['pending'] ?? 0);
                    $pDibayar = $deg($statusDist['dibayar'] ?? 0);
                    $pDikirim = $deg($statusDist['dikirim'] ?? 0);
                    $pSelesai = $deg($statusDist['selesai'] ?? 0);
                    $pBatal   = $deg($statusDist['batal']   ?? 0);
                @endphp
                <div class="ad-donut" style="--p-pending:{{ $pPending }}deg;--p-dibayar:{{ $pDibayar }}deg;--p-dikirim:{{ $pDikirim }}deg;--p-selesai:{{ $pSelesai }}deg;--p-batal:{{ $pBatal }}deg;">
                    <div class="ad-donut-label">
                        <span class="num">{{ $totalAllOrders }}</span>
                        <span class="lbl">Pesanan</span>
                    </div>
                </div>
                <div class="ad-legend">
                    @php
                        $legend = [
                            ['Menunggu', '#f59e0b', $statusDist['pending'] ?? 0],
                            ['Dikemas',  '#3b82f6', $statusDist['dibayar'] ?? 0],
                            ['Dikirim',  '#06b6d4', $statusDist['dikirim'] ?? 0],
                            ['Selesai',  '#16a34a', $statusDist['selesai'] ?? 0],
                            ['Batal',    '#ef4444', $statusDist['batal']   ?? 0],
                        ];
                    @endphp
                    @foreach ($legend as [$lbl, $col, $n])
                        <div class="ad-legend-item">
                            <span class="swatch" style="background:{{ $col }}"></span>
                            <span>{{ $lbl }}</span>
                            <span class="count">{{ $n }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- VERIFIKASI PENDING --}}
    <div class="col-lg-6">
        <div class="ad-panel">
            <div class="ad-panel-head">
                <h3><i class="ti ti-shield-check me-1 ico"></i> Petani Menunggu Verifikasi</h3>
                <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-sm btn-ghost-primary">Semua →</a>
            </div>
            @forelse ($pendingPetani as $p)
                @php
                    $initials = collect(explode(' ', trim($p->name)))->filter()->take(2)
                        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                @endphp
                <a href="{{ route('admin.verifikasi.show', $p) }}" class="ad-list-item text-decoration-none" style="color:inherit;">
                    <span class="ava">{{ $initials ?: 'P' }}</span>
                    <div class="flex-fill min-w-0">
                        <div class="name text-truncate">{{ $p->name }}</div>
                        <div class="meta">{{ $p->email }} · diajukan {{ $p->verification_submitted_at?->diffForHumans() ?? '—' }}</div>
                    </div>
                    <span class="badge bg-yellow-lt text-warning border-0">Pending</span>
                </a>
            @empty
                <div class="ad-empty">
                    <i class="ti ti-circle-check ico"></i>
                    Tidak ada pengajuan yang perlu ditinjau.
                </div>
            @endforelse
        </div>
    </div>

    {{-- PESANAN TERBARU --}}
    <div class="col-lg-6">
        <div class="ad-panel">
            <div class="ad-panel-head">
                <h3><i class="ti ti-shopping-bag me-1 ico"></i> Pesanan Terbaru</h3>
                <span class="text-secondary small">6 terakhir</span>
            </div>
            @forelse ($latestOrders as $o)
                <div class="ad-list-item">
                    <span class="ava" style="background: linear-gradient(135deg,#16a34a,#15803d);">
                        <i class="ti ti-shopping-bag"></i>
                    </span>
                    <div class="flex-fill min-w-0">
                        <div class="name text-truncate">{{ $o->code }}</div>
                        <div class="meta">{{ $o->user?->name ?? '—' }} · {{ $o->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="right">
                        <div class="fw-bold text-success small">Rp {{ number_format($o->total_harga, 0, ',', '.') }}</div>
                        <span class="badge {{ $o->status->badgeClass() }} mt-1">{{ $o->status->label() }}</span>
                    </div>
                </div>
            @empty
                <div class="ad-empty">
                    <i class="ti ti-shopping-bag-x ico"></i>
                    Belum ada pesanan.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- TOP PRODUCTS --}}
<div class="ad-panel">
    <div class="ad-panel-head">
        <h3><i class="ti ti-trophy me-1 ico"></i> Produk Paling Laris</h3>
        <a href="{{ route('admin.toko.index') }}" class="btn btn-sm btn-ghost-primary">Kelola →</a>
    </div>
    @forelse ($topProducts as $idx => $p)
        <div class="ad-list-item">
            <span class="ad-rank r{{ $idx + 1 <= 3 ? $idx + 1 : '' }}">{{ $idx + 1 }}</span>
            <img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="thumb">
            <div class="flex-fill min-w-0">
                <div class="name text-truncate">{{ $p->nama }}</div>
                <div class="meta">{{ $p->petani?->nama_usaha ?: $p->petani?->name }} · Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
            </div>
            <div class="right">
                <div class="fw-bold text-success">{{ number_format($p->sold_count ?? 0) }}</div>
                <div class="meta">terjual</div>
            </div>
        </div>
    @empty
        <div class="ad-empty">
            <i class="ti ti-package-off ico"></i>
            Belum ada produk yang terjual.
        </div>
    @endforelse
</div>
