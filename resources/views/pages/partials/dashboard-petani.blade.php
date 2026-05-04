@php
    use App\Enums\OrderStatus;
    use App\Models\OrderItem;
    use Illuminate\Support\Facades\DB;

    $verificationStatus = $user->verificationStatus();
    $showVerifyModal    = ! $user->is_verified && ! session('verifyModalDismissed');

    // === Statistik utama produk & pesanan ===
    $produkCount = $user->products()->count();
    $produkAktif = $user->products()->where('is_active', true)->count();
    $stokTotal   = (int) $user->products()->sum('stok');
    $lowStokCnt  = $user->products()->where('stok', '<=', 20)->count();

    $lowStok = $user->products()
        ->where('stok', '<=', 20)
        ->orderBy('stok')
        ->limit(5)
        ->get();

    // Pesanan masuk yang sudah dibayar pelanggan, milik (atau ada item milik) petani ini.
    $pesananBaru = OrderItem::with(['order.user', 'product'])
        ->whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order',   fn ($q) => $q->where('status', OrderStatus::Dibayar))
        ->latest('id')
        ->get()
        ->groupBy('order_id');

    $dikirimCount = OrderItem::whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order',   fn ($q) => $q->where('status', OrderStatus::Dikirim))
        ->distinct('order_id')->count('order_id');

    $selesaiCount = OrderItem::whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order',   fn ($q) => $q->where('status', OrderStatus::Selesai))
        ->distinct('order_id')->count('order_id');

    $batalCount = OrderItem::whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order',   fn ($q) => $q->where('status', OrderStatus::Batal))
        ->distinct('order_id')->count('order_id');

    // === Pendapatan: bulan ini vs bulan lalu ===
    $startOfMonth     = now()->startOfMonth();
    $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth();
    $endOfLastMonth   = now()->subMonthNoOverflow()->endOfMonth();

    $sumRevenue = function ($from, $to) use ($user) {
        return (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('products.user_id', $user->id)
            ->where('orders.status', OrderStatus::Selesai->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('SUM(order_items.harga * order_items.jumlah) as total')
            ->value('total') ?? 0;
    };

    $revenueBulanIni  = $sumRevenue($startOfMonth, now());
    $revenueBulanLalu = $sumRevenue($startOfLastMonth, $endOfLastMonth);
    $revenueAllTime   = $sumRevenue(now()->subYears(20), now());

    $deltaPct = $revenueBulanLalu > 0
        ? (int) round((($revenueBulanIni - $revenueBulanLalu) / $revenueBulanLalu) * 100)
        : ($revenueBulanIni > 0 ? 100 : 0);

    // === Pendapatan per hari (7 hari terakhir) untuk mini-chart ===
    $rangeStart = now()->subDays(6)->startOfDay();
    $dailyRevenue = DB::table('order_items')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->join('products', 'products.id', '=', 'order_items.product_id')
        ->where('products.user_id', $user->id)
        ->where('orders.status', OrderStatus::Selesai->value)
        ->where('orders.created_at', '>=', $rangeStart)
        ->selectRaw('DATE(orders.created_at) as day, SUM(order_items.harga * order_items.jumlah) as total')
        ->groupBy('day')
        ->pluck('total', 'day')
        ->all();

    $last7Days = collect(range(6, 0))->map(function ($d) use ($dailyRevenue) {
        $date = now()->subDays($d)->startOfDay();
        return [
            'label'   => $date->translatedFormat('D'),
            'date'    => $date->format('d M'),
            'revenue' => (float) ($dailyRevenue[$date->format('Y-m-d')] ?? 0),
        ];
    });
    $maxRevenue = max($last7Days->max('revenue'), 1);

    // === Top 5 produk laris milik petani ===
    $topProducts = $user->products()
        ->where('sold_count', '>', 0)
        ->orderByDesc('sold_count')
        ->limit(5)
        ->get();

    // === Sapaan ===
    $hour     = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));

    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $rp = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');
@endphp

<style>
    /* === Hero === */
    .pt-hero{
        background:
            radial-gradient(1200px 280px at 110% -20%, rgba(255,255,255,.18), transparent 60%),
            radial-gradient(700px 240px at -10% 110%, rgba(110,231,183,.35), transparent 55%),
            linear-gradient(135deg,#064e3b 0%, #047857 45%, #10b981 100%);
        color:#fff; border-radius:18px; padding:24px 28px; margin-bottom:18px;
        position:relative; overflow:hidden;
    }
    .pt-hero::before{
        content:""; position:absolute; right:-60px; top:-50px; width:240px; height:240px;
        background: radial-gradient(circle, rgba(255,255,255,.18), transparent 70%); border-radius:50%;
    }
    .pt-hero .greet{ font-size:.84rem; opacity:.9; letter-spacing:.02em; }
    .pt-hero h1{ font-size:1.6rem; font-weight:800; margin:2px 0 0; letter-spacing:-.02em; line-height:1.15; }
    .pt-hero .desc{ font-size:.92rem; opacity:.93; max-width:560px; margin-top:8px; }
    .pt-hero .pt-avatar{
        width:64px; height:64px; border-radius:50%;
        background: rgba(255,255,255,.2); backdrop-filter: blur(6px);
        display:inline-flex; align-items:center; justify-content:center;
        font-weight:800; font-size:1.4rem; flex-shrink:0;
        box-shadow: 0 8px 24px -8px rgba(0,0,0,.25);
        border:1px solid rgba(255,255,255,.25);
    }
    .pt-hero .pt-meta{
        display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;
        background: rgba(255,255,255,.14); backdrop-filter: blur(8px);
        padding:8px 14px; border-radius:999px; font-size:.78rem; font-weight:600;
        border:1px solid rgba(255,255,255,.18);
    }
    .pt-hero .pt-meta i{ font-size:.95rem; }
    .pt-hero .btn-cta{
        background:#fff; color:#047857; border:0; font-weight:700;
        padding:.55rem 1.1rem; border-radius:10px;
        box-shadow: 0 8px 18px -8px rgba(0,0,0,.3);
    }
    .pt-hero .btn-cta:hover{ background:#ecfdf5; }

    /* === Stat cards === */
    .pt-stat{
        background:#fff; border:1px solid #e5e7eb; border-radius:14px;
        padding:18px 20px; height:100%;
        transition: transform .15s, box-shadow .15s, border-color .15s;
        position:relative; overflow:hidden;
    }
    .pt-stat:hover{ transform: translateY(-2px); box-shadow:0 10px 22px -12px rgba(15,23,42,.14); }
    .pt-stat .ico{
        width:46px; height:46px; border-radius:12px;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:1.4rem; margin-bottom:10px;
    }
    .pt-stat .lbl{ font-size:.78rem; color:#64748b; font-weight:600; }
    .pt-stat .val{ font-size:1.85rem; font-weight:800; color:#0f172a; line-height:1.1; margin-top:2px; letter-spacing:-.01em; }
    .pt-stat .val.is-rp{ font-size:1.45rem; }
    .pt-stat .sub{ font-size:.74rem; color:#94a3b8; margin-top:6px; }
    .pt-stat .sub strong{ color:#0f172a; font-weight:700; }
    .pt-stat .delta{
        display:inline-flex; align-items:center; gap:3px;
        padding:2px 8px; border-radius:999px;
        font-size:.7rem; font-weight:700; margin-top:6px;
    }
    .pt-stat .delta.up   { background:#dcfce7; color:#15803d; }
    .pt-stat .delta.down { background:#fee2e2; color:#b91c1c; }
    .pt-stat .delta.flat { background:#f1f5f9; color:#475569; }
    .pt-stat .ico.b1{ background:#ecfdf5; color:#047857; }
    .pt-stat .ico.b2{ background:#dbeafe; color:#1d4ed8; }
    .pt-stat .ico.b3{ background:#fef3c7; color:#b45309; }
    .pt-stat .ico.b4{ background:#fce7f3; color:#be185d; }
    .pt-stat.is-warn{ border-color:#fcd34d; background: linear-gradient(180deg,#fffbeb 0%,#fff 60%); }

    /* === Quick actions === */
    .pt-actions{ display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:10px; margin-bottom:18px; }
    .pt-action{
        background:#fff; border:1px solid #e5e7eb; border-radius:12px;
        padding:14px; display:flex; align-items:center; gap:10px;
        text-decoration:none; color:#0f172a;
        transition: border-color .15s, transform .15s, background .15s;
    }
    .pt-action:hover{ border-color:#10b981; background:#ecfdf5; transform: translateY(-2px); color:#0f172a; }
    .pt-action .ico{
        width:36px; height:36px; border-radius:10px; flex-shrink:0;
        background:#ecfdf5; color:#047857;
        display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem;
    }
    .pt-action .name{ font-weight:700; font-size:.86rem; line-height:1.2; }
    .pt-action .desc{ font-size:.72rem; color:#94a3b8; margin-top:1px; }
    .pt-action.urgent{ border-color:#fbbf24; background:#fffbeb; }
    .pt-action.urgent .ico{ background:#fef3c7; color:#b45309; }
    .pt-action.urgent .badge{
        margin-left:auto; background:#dc2626; color:#fff; font-weight:700;
        padding:3px 8px; border-radius:999px; font-size:.7rem;
    }

    /* === Panel === */
    .pt-panel{
        background:#fff; border:1px solid #e5e7eb; border-radius:14px;
        overflow:hidden; height:100%;
    }
    .pt-panel-head{
        padding:14px 18px; border-bottom:1px solid #f1f5f9;
        display:flex; justify-content:space-between; align-items:center; gap:10px;
        background: linear-gradient(180deg,#fbfdfc 0%,#fff 100%);
    }
    .pt-panel-head h3{ font-size:.95rem; font-weight:700; color:#0f172a; margin:0; }
    .pt-panel-head .ico{ color:#047857; }

    /* === Revenue mini chart (SVG path) === */
    .pt-chart-wrap{ padding:8px 18px 16px; }
    .pt-chart{ width:100%; height:160px; display:block; }
    .pt-chart .grid{ stroke:#eef2f7; stroke-width:1; }
    .pt-chart .area{ fill: url(#ptGrad); opacity:.85; }
    .pt-chart .line{ fill:none; stroke:#10b981; stroke-width:2.4; stroke-linejoin:round; stroke-linecap:round; }
    .pt-chart .dot{ fill:#fff; stroke:#10b981; stroke-width:2; }
    .pt-chart-axis{
        display:grid; grid-template-columns: repeat(7, 1fr); gap:4px;
        margin-top:6px; text-align:center;
    }
    .pt-chart-axis .day{ font-size:.7rem; color:#475569; font-weight:700; }
    .pt-chart-axis .date{ font-size:.66rem; color:#94a3b8; }

    /* === List item === */
    .pt-list-item{
        padding:12px 18px;
        display:flex; align-items:center; gap:12px;
        border-top:1px solid #f1f5f9;
    }
    .pt-list-item:hover{ background:#f8fafc; }
    .pt-list-item .thumb{ width:42px; height:42px; border-radius:10px; object-fit:cover; flex-shrink:0; background:#f6f8fa; border:1px solid #e5e7eb; }
    .pt-list-item .name{ font-weight:600; color:#0f172a; font-size:.88rem; }
    .pt-list-item .meta{ font-size:.72rem; color:#94a3b8; margin-top:2px; }
    .pt-list-item .right{ margin-left:auto; text-align:right; flex-shrink:0; }
    .pt-rank{
        width:24px; height:24px; border-radius:50%;
        background:#f1f5f9; color:#475569; font-weight:800; font-size:.75rem;
        display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .pt-rank.r1{ background: linear-gradient(135deg,#fbbf24,#f59e0b); color:#fff; }
    .pt-rank.r2{ background: linear-gradient(135deg,#cbd5e1,#94a3b8); color:#fff; }
    .pt-rank.r3{ background: linear-gradient(135deg,#fdba74,#f97316); color:#fff; }

    .pt-empty{
        padding:32px 16px; text-align:center; color:#94a3b8; font-size:.85rem;
    }
    .pt-empty .ico{ font-size:2.2rem; color:#cbd5e1; display:block; margin-bottom:8px; }

    /* === Status chips for orders === */
    .pt-status-row{ display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:10px; padding:14px 18px; }
    .pt-status-chip{
        padding:12px 14px; border-radius:12px; border:1px solid #e5e7eb;
        background:#fff;
    }
    .pt-status-chip .label{ font-size:.72rem; color:#64748b; font-weight:600; }
    .pt-status-chip .num{ font-size:1.4rem; font-weight:800; color:#0f172a; line-height:1; margin-top:4px; }
    .pt-status-chip.s-dibayar { background: #eff6ff; border-color:#bfdbfe; }
    .pt-status-chip.s-dikirim { background: #ecfeff; border-color:#a5f3fc; }
    .pt-status-chip.s-selesai { background: #ecfdf5; border-color:#a7f3d0; }
    .pt-status-chip.s-batal   { background: #fef2f2; border-color:#fecaca; }
</style>

{{-- HERO --}}
<div class="pt-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3" style="position:relative;z-index:1;min-width:0;">
        <div class="pt-avatar">{{ $initials ?: 'P' }}</div>
        <div style="min-width:0">
            <div class="greet">{{ $greeting }} 👋</div>
            <h1 class="text-truncate">{{ $user->name }}</h1>
            <p class="desc mb-0">
                @if ($user->is_verified)
                    Akun Anda sudah terverifikasi — mari kelola toko {{ $user->nama_usaha ? '"'.$user->nama_usaha.'"' : 'Anda' }} dengan lebih efisien.
                @else
                    Belum terverifikasi. Lengkapi data toko agar produk bisa tampil di katalog publik.
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2" style="position:relative;z-index:1;">
        <span class="pt-meta">
            <i class="ti ti-calendar"></i>{{ now()->translatedFormat('l, d F') }}
        </span>
        <a href="{{ route('petani.produk.create') }}" class="btn btn-cta">
            <i class="ti ti-plus me-1"></i>Tambah Produk
        </a>
    </div>
</div>

{{-- Verifikasi banner: hanya muncul kalau belum verified --}}
@if (! $user->is_verified)
    <div class="row row-cards mb-3">
        @if ($verificationStatus === 'rejected')
            <div class="col-12">
                <div class="card position-relative overflow-hidden">
                    <div class="card-status-start bg-danger"></div>
                    <div class="card-body d-flex align-items-start gap-3 flex-wrap">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                              style="width:48px;height:48px;background:#fee2e2;color:#b91c1c">
                            <i class="ti ti-circle-x" style="font-size:1.6rem"></i>
                        </span>
                        <div class="flex-fill" style="min-width:0">
                            <div class="h3 text-danger mb-1">Pengajuan Verifikasi Ditolak</div>
                            <div class="text-secondary small mb-2">
                                Admin menolak pengajuan verifikasi Anda. Perbaiki data, lalu ajukan ulang.
                            </div>
                            @if ($user->verification_note)
                                <div class="alert alert-danger py-2 small mb-2" role="alert">
                                    <strong><i class="ti ti-message-circle-exclamation me-1"></i>Catatan Admin:</strong>
                                    {{ $user->verification_note }}
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('petani.verifikasi.index') }}" class="btn btn-danger">
                            <i class="ti ti-refresh me-1"></i>Ajukan Ulang
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12">
                <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                    <i class="ti ti-alert-triangle me-2" style="font-size:1.25rem"></i>
                    <div class="flex-fill">
                        <div class="fw-semibold">Akun belum terverifikasi</div>
                        <div class="small">
                            @if ($verificationStatus === 'pending')
                                Pengajuan Anda sedang direview admin. Produk Anda belum tampil di katalog hingga disetujui.
                            @else
                                Anda bisa menambah produk sekarang, tapi belum tampil di katalog sampai verifikasi disetujui.
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('petani.verifikasi.index') }}" class="btn btn-warning btn-sm ms-3">
                        {{ $verificationStatus === 'pending' ? 'Lihat Status' : 'Lengkapi Verifikasi' }}
                    </a>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- STAT CARDS --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="pt-stat">
            <div class="ico b1"><i class="ti ti-package"></i></div>
            <div class="lbl">Produk Saya</div>
            <div class="val">{{ number_format($produkCount) }}</div>
            <div class="sub"><strong>{{ $produkAktif }}</strong> aktif · stok total <strong>{{ number_format($stokTotal) }}</strong> unit</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="pt-stat {{ $pesananBaru->count() > 0 ? 'is-warn' : '' }}">
            <div class="ico b3"><i class="ti ti-shopping-bag"></i></div>
            <div class="lbl">Pesanan Menunggu</div>
            <div class="val">{{ number_format($pesananBaru->count()) }}</div>
            <div class="sub">
                @if ($pesananBaru->count() > 0)
                    <strong class="text-warning">Segera diproses</strong>
                @else
                    aman, tidak ada antrean
                @endif
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="pt-stat">
            <div class="ico b2"><i class="ti ti-truck-delivery"></i></div>
            <div class="lbl">Sedang Dikirim / Selesai</div>
            <div class="val">{{ number_format($dikirimCount + $selesaiCount) }}</div>
            <div class="sub"><strong>{{ $dikirimCount }}</strong> dikirim · <strong>{{ $selesaiCount }}</strong> selesai</div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="pt-stat">
            <div class="ico b4"><i class="ti ti-cash-banknote"></i></div>
            <div class="lbl">Pendapatan Bulan Ini</div>
            <div class="val is-rp">{{ $rp($revenueBulanIni) }}</div>
            @if ($deltaPct > 0)
                <span class="delta up"><i class="ti ti-arrow-up-right"></i>{{ $deltaPct }}% vs bulan lalu</span>
            @elseif ($deltaPct < 0)
                <span class="delta down"><i class="ti ti-arrow-down-right"></i>{{ abs($deltaPct) }}% vs bulan lalu</span>
            @else
                <span class="delta flat"><i class="ti ti-minus"></i>setara bulan lalu</span>
            @endif
            <div class="sub mt-1">total all-time <strong>{{ $rp($revenueAllTime) }}</strong></div>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="pt-actions">
    <a href="{{ route('petani.produk.create') }}" class="pt-action">
        <span class="ico"><i class="ti ti-plus"></i></span>
        <div>
            <div class="name">Tambah Produk</div>
            <div class="desc">Listing baru untuk katalog</div>
        </div>
    </a>
    <a href="{{ route('petani.pesanan.index', ['status' => 'dibayar']) }}" class="pt-action {{ $pesananBaru->count() > 0 ? 'urgent' : '' }}">
        <span class="ico"><i class="ti ti-shopping-bag"></i></span>
        <div>
            <div class="name">Pesanan Masuk</div>
            <div class="desc">Proses & kirim barang</div>
        </div>
        @if ($pesananBaru->count() > 0)
            <span class="badge">{{ $pesananBaru->count() }}</span>
        @endif
    </a>
    <a href="{{ route('petani.produk.index') }}" class="pt-action {{ $lowStokCnt > 0 ? 'urgent' : '' }}">
        <span class="ico"><i class="ti ti-package"></i></span>
        <div>
            <div class="name">Kelola Produk</div>
            <div class="desc">@if ($lowStokCnt > 0) {{ $lowStokCnt }} stok kritis @else Semua aman @endif</div>
        </div>
    </a>
    <a href="{{ route('petani.laporan.index') }}" class="pt-action">
        <span class="ico"><i class="ti ti-file-invoice"></i></span>
        <div>
            <div class="name">Laporan</div>
            <div class="desc">Riwayat transaksi</div>
        </div>
    </a>
    <a href="{{ route('profile.edit') }}" class="pt-action">
        <span class="ico"><i class="ti ti-user-circle"></i></span>
        <div>
            <div class="name">Profil Toko</div>
            <div class="desc">Atur data & alamat</div>
        </div>
    </a>
</div>

{{-- ROW: chart pendapatan + status pesanan --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="pt-panel">
            <div class="pt-panel-head">
                <h3><i class="ti ti-chart-line me-1 ico"></i> Pendapatan 7 Hari Terakhir</h3>
                <span class="text-secondary small">{{ $rp($last7Days->sum('revenue')) }}</span>
            </div>
            <div class="pt-chart-wrap">
                @if ($last7Days->sum('revenue') > 0)
                    @php
                        // Normalisasi titik ke koordinat SVG (viewBox 700x140).
                        $w = 700; $h = 140; $px = 30; $py = 14;
                        $stepX = ($w - 2*$px) / 6;
                        $points = $last7Days->values()->map(function ($d, $i) use ($maxRevenue, $stepX, $h, $py, $px) {
                            $x = $px + $stepX * $i;
                            $y = $py + ($h - 2*$py) * (1 - ($d['revenue'] / $maxRevenue));
                            return ['x' => $x, 'y' => $y, 'd' => $d];
                        });
                        $linePath = $points->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L').' '.round($p['x'], 2).' '.round($p['y'], 2))->implode(' ');
                        $areaPath = 'M '.round($points[0]['x'], 2).' '.($h - $py).' '.
                            $points->map(fn ($p) => 'L '.round($p['x'], 2).' '.round($p['y'], 2))->implode(' ').
                            ' L '.round($points->last()['x'], 2).' '.($h - $py).' Z';
                    @endphp
                    <svg class="pt-chart" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="ptGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%"  stop-color="#10b981" stop-opacity=".35"/>
                                <stop offset="100%" stop-color="#10b981" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        @for ($i = 0; $i <= 3; $i++)
                            <line class="grid" x1="{{ $px }}" y1="{{ $py + (($h - 2*$py) * $i / 3) }}" x2="{{ $w - $px }}" y2="{{ $py + (($h - 2*$py) * $i / 3) }}"/>
                        @endfor
                        <path class="area" d="{{ $areaPath }}"/>
                        <path class="line" d="{{ $linePath }}"/>
                        @foreach ($points as $p)
                            <circle class="dot" cx="{{ round($p['x'], 2) }}" cy="{{ round($p['y'], 2) }}" r="3.5">
                                <title>{{ $p['d']['date'] }} — {{ $rp($p['d']['revenue']) }}</title>
                            </circle>
                        @endforeach
                    </svg>
                @else
                    <div class="pt-empty">
                        <i class="ti ti-chart-line ico"></i>
                        Belum ada penjualan 7 hari terakhir.
                    </div>
                @endif
                <div class="pt-chart-axis">
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

    <div class="col-lg-5">
        <div class="pt-panel">
            <div class="pt-panel-head">
                <h3><i class="ti ti-list-check me-1 ico"></i> Ringkasan Status Pesanan</h3>
                <a href="{{ route('petani.pesanan.index') }}" class="btn btn-sm btn-ghost-success">Semua →</a>
            </div>
            <div class="pt-status-row">
                <div class="pt-status-chip s-dibayar">
                    <div class="label"><i class="ti ti-package me-1"></i>Dikemas</div>
                    <div class="num">{{ $pesananBaru->count() }}</div>
                </div>
                <div class="pt-status-chip s-dikirim">
                    <div class="label"><i class="ti ti-truck me-1"></i>Dikirim</div>
                    <div class="num">{{ $dikirimCount }}</div>
                </div>
                <div class="pt-status-chip s-selesai">
                    <div class="label"><i class="ti ti-circle-check me-1"></i>Selesai</div>
                    <div class="num">{{ $selesaiCount }}</div>
                </div>
                <div class="pt-status-chip s-batal">
                    <div class="label"><i class="ti ti-ban me-1"></i>Batal</div>
                    <div class="num">{{ $batalCount }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW: pesanan perlu diproses + top produk --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="pt-panel">
            <div class="pt-panel-head">
                <h3><i class="ti ti-clipboard-check me-1 ico"></i> Pesanan Perlu Diproses</h3>
                <a href="{{ route('petani.pesanan.index', ['status' => 'dibayar']) }}" class="btn btn-sm btn-ghost-success">Semua →</a>
            </div>
            @forelse ($pesananBaru->take(5) as $orderId => $items)
                @php($order = $items->first()->order)
                <div class="pt-list-item">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                          style="width:42px;height:42px;background:#dcfce7;color:#15803d">
                        <i class="ti ti-shopping-bag"></i>
                    </span>
                    <div class="flex-fill" style="min-width:0">
                        <div class="name text-truncate">Order #{{ $order->id }} · {{ $order->user->name }}</div>
                        <div class="meta text-truncate">{{ $items->pluck('product.nama')->implode(', ') }}</div>
                    </div>
                    <div class="right">
                        <div class="fw-bold text-success small">{{ $rp($items->sum(fn ($i) => $i->harga * $i->jumlah)) }}</div>
                        <span class="badge {{ $order->status->badgeClass() }} mt-1">{{ $order->status->label() }}</span>
                    </div>
                </div>
            @empty
                <div class="pt-empty">
                    <i class="ti ti-checks ico"></i>
                    Tidak ada pesanan yang perlu diproses.
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="pt-panel">
            <div class="pt-panel-head">
                <h3><i class="ti ti-trophy me-1 ico"></i> Produk Paling Laris</h3>
                <a href="{{ route('petani.produk.index') }}" class="btn btn-sm btn-ghost-success">Kelola →</a>
            </div>
            @forelse ($topProducts as $idx => $p)
                <div class="pt-list-item">
                    <span class="pt-rank r{{ $idx + 1 <= 3 ? $idx + 1 : '' }}">{{ $idx + 1 }}</span>
                    <img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="thumb" loading="lazy">
                    <div class="flex-fill" style="min-width:0">
                        <div class="name text-truncate">{{ $p->nama }}</div>
                        <div class="meta">{{ $rp($p->harga) }} · stok {{ $p->stok }}</div>
                    </div>
                    <div class="right">
                        <div class="fw-bold text-success">{{ number_format($p->sold_count) }}</div>
                        <div class="meta">terjual</div>
                    </div>
                </div>
            @empty
                <div class="pt-empty">
                    <i class="ti ti-package-off ico"></i>
                    Belum ada produk yang terjual.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- STOK MENIPIS — full-width agar petani gampang baca --}}
<div class="pt-panel">
    <div class="pt-panel-head">
        <h3><i class="ti ti-alert-triangle me-1 ico" style="color:#b45309 !important"></i> Stok Menipis (≤ 20)</h3>
        <a href="{{ route('petani.produk.index', ['stock' => 'low']) }}" class="btn btn-sm btn-ghost-warning">Lihat semua →</a>
    </div>
    @forelse ($lowStok as $p)
        <div class="pt-list-item">
            <img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="thumb" loading="lazy">
            <div class="flex-fill" style="min-width:0">
                <div class="name text-truncate">{{ $p->nama }}</div>
                <div class="meta">{{ $p->category?->nama ?? '—' }} · {{ $rp($p->harga) }}</div>
            </div>
            <div class="right">
                <span class="badge {{ $p->stok == 0 ? 'bg-red' : ($p->stok <= 5 ? 'bg-orange' : 'bg-yellow') }}">
                    {{ $p->stok }} unit
                </span>
            </div>
        </div>
    @empty
        <div class="pt-empty">
            <i class="ti ti-circle-check ico" style="color:#16a34a !important"></i>
            Semua stok produk Anda dalam batas aman.
        </div>
    @endforelse
</div>

@if ($showVerifyModal)
    <div class="modal modal-blur fade" id="verifyReminderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}"></div>
                <div class="modal-body text-center py-4">
                    <i class="ti ti-{{ $verificationStatus === 'pending' ? 'clock' : ($verificationStatus === 'rejected' ? 'circle-x' : 'shield-check') }} mb-2 text-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}" style="font-size:3rem"></i>
                    <h3>
                        @if ($verificationStatus === 'pending')
                            Verifikasi sedang direview
                        @elseif ($verificationStatus === 'rejected')
                            Pengajuan verifikasi ditolak
                        @else
                            Lengkapi verifikasi akun
                        @endif
                    </h3>
                    <div class="text-secondary">
                        @if ($verificationStatus === 'pending')
                            Tim admin sedang memeriksa data Anda. Selama masa review, produk Anda belum tampil di katalog publik.
                        @elseif ($verificationStatus === 'rejected')
                            Silakan lihat catatan admin lalu perbaiki data pengajuan Anda.
                        @else
                            Agar produk Anda tampil di katalog dan bisa dibeli pelanggan, lengkapi data usaha & KTP terlebih dahulu. Verifikasi akan direview admin.
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('petani.verifikasi.dismiss') }}" class="w-auto me-auto">
                        @csrf
                        <button type="submit" class="btn btn-link link-secondary">Nanti saja</button>
                    </form>
                    <a href="{{ route('petani.verifikasi.index') }}" class="btn btn-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}">
                        {{ $verificationStatus === 'pending' ? 'Lihat Status' : ($verificationStatus === 'rejected' ? 'Perbaiki Pengajuan' : 'Lengkapi Sekarang') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('verifyReminderModal');
                if (el && window.bootstrap) {
                    new bootstrap.Modal(el).show();
                }
            });
        </script>
    @endpush
@endif
