@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    use App\Enums\OrderStatus;
    $title  = 'Pesanan Saya';
    $active = 'pelanggan.pesanan';

    // Helper: posisi step untuk progress bar order.
    // Pending=1, Dibayar=2, Dikirim=3, Selesai=4. Batal = -1 (terminal merah).
    $stepIndex = fn (OrderStatus $s) => match ($s) {
        OrderStatus::Pending => 1,
        OrderStatus::Dibayar => 2,
        OrderStatus::Dikirim => 3,
        OrderStatus::Selesai => 4,
        OrderStatus::Batal   => -1,
    };
    $steps = [
        [OrderStatus::Pending, 'Menunggu Bayar', 'clock'],
        [OrderStatus::Dibayar, 'Dikemas',        'package'],
        [OrderStatus::Dikirim, 'Dikirim',        'truck-delivery'],
        [OrderStatus::Selesai, 'Selesai',        'circle-check'],
    ];

    // Status tabs (icon + warna untuk filter cepat).
    $tabs = [
        ['key' => null,                       'label' => 'Semua',          'icon' => 'list',           'count' => $totalAll],
        ['key' => OrderStatus::Pending->value,'label' => 'Menunggu Bayar', 'icon' => 'clock',          'count' => $statusCounts[OrderStatus::Pending->value] ?? 0],
        ['key' => OrderStatus::Dibayar->value,'label' => 'Dikemas',        'icon' => 'package',        'count' => $statusCounts[OrderStatus::Dibayar->value] ?? 0],
        ['key' => OrderStatus::Dikirim->value,'label' => 'Dikirim',        'icon' => 'truck-delivery', 'count' => $statusCounts[OrderStatus::Dikirim->value] ?? 0],
        ['key' => OrderStatus::Selesai->value,'label' => 'Selesai',        'icon' => 'circle-check',   'count' => $statusCounts[OrderStatus::Selesai->value] ?? 0],
        ['key' => OrderStatus::Batal->value,  'label' => 'Dibatalkan',     'icon' => 'circle-x',       'count' => $statusCounts[OrderStatus::Batal->value] ?? 0],
    ];
    $activeStatus = request('status');
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            /* === Header Hero === */
            .ps-hero {
                background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
                border:1px solid #d1fae5; border-radius: 14px;
                padding: 18px 22px; margin-bottom: 16px;
                display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
            }
            .ps-hero h1 { margin:0; font-size:1.35rem; font-weight:800; color:#064e3b; letter-spacing:-.01em; }
            .ps-hero .sub { color:#047857; font-size:.85rem; margin-top:2px; }
            .ps-hero .ico { width:44px; height:44px; border-radius:12px; background:#fff; color:#16a34a; display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; box-shadow:0 4px 12px -4px rgba(16,185,129,.3); }

            /* === Status tabs === */
            .ps-tabs { display:flex; gap:6px; overflow-x:auto; padding:8px 4px; margin: 0 -4px 14px; scrollbar-width: thin; }
            .ps-tab {
                display:inline-flex; align-items:center; gap:8px;
                padding:10px 14px; border-radius:999px;
                background:#fff; border:1px solid #e2e8f0;
                color:#475569; font-weight:600; font-size:.85rem;
                white-space:nowrap; text-decoration:none;
                transition: all .15s ease;
                flex-shrink:0;
            }
            .ps-tab:hover { background:#f8fafc; border-color:#cbd5e1; color:#0f172a; }
            .ps-tab.is-active { background:#16a34a; border-color:#16a34a; color:#fff; }
            .ps-tab.is-active .badge { background:rgba(255,255,255,.25); color:#fff; }
            .ps-tab .badge { background:#f1f5f9; color:#475569; font-size:.7rem; padding:2px 8px; border-radius:999px; font-weight:700; min-width:20px; }
            .ps-tab .ti { font-size:1.05rem; }

            /* === Search bar === */
            .ps-search-wrap {
                background:#fff; border:1px solid #e2e8f0; border-radius:12px;
                padding:6px; display:flex; gap:6px; align-items:center; margin-bottom:14px;
                transition: border-color .15s, box-shadow .15s;
            }
            .ps-search-wrap:focus-within { border-color:#16a34a; box-shadow:0 0 0 4px rgba(22,163,74,.1); }
            .ps-search-wrap .ti-search { color:#94a3b8; margin-left:10px; font-size:1.1rem; }
            .ps-search-wrap input { border:0; outline:0; flex:1; padding:8px 4px; background:transparent; font-size:.92rem; }
            .ps-search-wrap input:focus { box-shadow:none; }
            .ps-search-wrap select { border:0; outline:0; background:#f8fafc; padding:8px 12px; border-radius:8px; font-size:.85rem; color:#475569; cursor:pointer; }
            .ps-search-wrap button { background:#16a34a; color:#fff; border:0; padding:8px 18px; border-radius:8px; font-weight:600; font-size:.88rem; cursor:pointer; }
            .ps-search-wrap button:hover { background:#15803d; }

            /* === Order item card === */
            .order-item {
                background:#fff; border:1px solid #e2e8f0; border-radius:12px;
                margin-bottom:10px; overflow:hidden;
                transition: border-color .15s ease, box-shadow .15s ease;
            }
            .order-item:hover { border-color:#cbd5e1; }
            .order-item[open] { border-color:#16a34a; box-shadow: 0 8px 24px -8px rgba(15,23,42,.08); }

            /* Status accent stripe (4px) */
            .order-item { position:relative; }
            .order-item::before {
                content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
                background:#cbd5e1; transition: background .15s;
            }
            .order-item.s-pending::before { background:#f59e0b; }
            .order-item.s-dibayar::before { background:#3b82f6; }
            .order-item.s-dikirim::before { background:#06b6d4; }
            .order-item.s-selesai::before { background:#16a34a; }
            .order-item.s-batal::before   { background:#ef4444; }

            /* Summary row */
            .order-summary {
                display:grid; align-items:center; gap:14px;
                grid-template-columns: auto minmax(0,1fr) auto auto 32px;
                padding:14px 18px 14px 22px; cursor:pointer;
                list-style:none; user-select:none;
            }
            .order-summary::-webkit-details-marker { display:none; }
            .order-summary::marker { content:''; }
            .order-summary:hover { background:#f8fafc; }

            /* Thumbnail stack */
            .os-thumbs { display:flex; align-items:center; flex-shrink:0; }
            .os-thumbs .thumb {
                width:42px; height:42px; border-radius:10px;
                object-fit:cover; background:#f6f8fa;
                border:2px solid #fff; box-shadow:0 0 0 1px #e5e7eb;
                margin-left:-12px; flex-shrink:0;
            }
            .os-thumbs .thumb:first-child { margin-left:0; }
            .os-thumbs .more {
                width:42px; height:42px; border-radius:10px;
                background:#f1f5f9; color:#475569; font-weight:700; font-size:.78rem;
                border:2px solid #fff; box-shadow:0 0 0 1px #e5e7eb;
                display:inline-flex; align-items:center; justify-content:center;
                margin-left:-12px;
            }

            .os-info { min-width:0; }
            .os-info .product-summary { font-weight:600; color:#0f172a; font-size:.92rem; margin-bottom:3px;
                overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
            }
            .os-info .meta { font-size:.76rem; color:#94a3b8; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
            .os-info .meta .code { font-family:'SF Mono','Menlo',monospace; color:#64748b; }
            .os-info .meta .dot { color:#cbd5e1; }

            .os-status { display:flex; flex-direction:column; align-items:flex-end; gap:4px; flex-shrink:0; }
            .os-status .badge { font-weight:700; padding:5px 10px; font-size:.72rem; }
            .os-status .countdown { font-size:.7rem; color:#dc2626; font-weight:600; display:inline-flex; align-items:center; gap:3px; }

            .os-total { text-align:right; flex-shrink:0; }
            .os-total .lbl { display:block; font-size:.7rem; color:#94a3b8; font-weight:500; margin-bottom:2px; }
            .os-total .val { font-weight:700; color:#15803d; font-size:1rem; white-space:nowrap; }

            .os-chev {
                width:32px; height:32px; border-radius:50%;
                display:inline-flex; align-items:center; justify-content:center;
                background:#f1f5f9; color:#475569;
                transition: transform .25s ease, background .15s, color .15s;
                flex-shrink:0;
            }
            .order-item[open] .os-chev { transform: rotate(180deg); background:#dcfce7; color:#15803d; }

            /* Body */
            .order-body { border-top:1px solid #f1f5f9; }

            .order-line { display:flex; align-items:center; gap:14px; padding:12px 22px; }
            .order-line + .order-line { border-top:1px dashed #f1f5f9; }
            .order-line .thumb { width:48px; height:48px; border-radius:10px; object-fit:cover; background:#f6f8fa; border:1px solid #e5e7eb; flex-shrink:0; }
            .order-line .name { font-weight:600; color:#0f172a; font-size:.9rem; }
            .order-line .muted { color:#94a3b8; font-style:italic; }
            .order-line .qty  { color:#64748b; font-size:.8rem; }
            .order-line .sub  { font-weight:700; color:#0f172a; white-space:nowrap; font-size:.92rem; }

            /* Address card di expanded */
            .order-addr {
                margin: 12px 22px; padding:12px 14px;
                background:#f8fafc; border:1px dashed #cbd5e1; border-radius:10px;
                display:flex; gap:10px; align-items:flex-start;
            }
            .order-addr .ico { color:#16a34a; flex-shrink:0; margin-top:2px; }
            .order-addr .name { font-weight:600; font-size:.88rem; color:#0f172a; }
            .order-addr .text { font-size:.82rem; color:#64748b; line-height:1.45; margin-top:2px; }

            /* Footer aksi */
            .order-foot { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:14px 22px; background:#fafbfc; border-top:1px solid #f1f5f9; flex-wrap:wrap; }
            .order-foot .status-msg { font-size:.85rem; color:#475569; display:inline-flex; align-items:center; gap:6px; }
            .order-foot .actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
            .order-foot .countdown-tag {
                background:#fef3c7; color:#92400e; padding:5px 10px; border-radius:8px;
                font-size:.78rem; font-weight:600; display:inline-flex; align-items:center; gap:5px;
            }
            .order-foot .countdown-tag.is-warn { background:#fee2e2; color:#991b1b; }

            /* Status bar (stepper) */
            .order-progress { display:flex; align-items:flex-start; gap:0; padding:18px 22px 10px; background:#fff; }
            .op-step { flex:1; display:flex; flex-direction:column; align-items:center; gap:6px; position:relative; min-width:0; }
            .op-step .dot {
                width:36px; height:36px; border-radius:50%;
                display:inline-flex; align-items:center; justify-content:center;
                background:#e2e8f0; color:#94a3b8;
                border:2px solid transparent; font-size:1.05rem;
                transition: background .25s, color .25s, border-color .25s, transform .25s, box-shadow .25s;
                z-index:1; flex-shrink:0;
            }
            .op-step .lbl { font-size:.74rem; color:#94a3b8; font-weight:600; text-align:center; line-height:1.2; }
            .op-step + .op-step::before {
                content:""; position:absolute; left:-50%; top:17px;
                width:100%; height:3px; background:#e2e8f0; z-index:0;
            }
            .op-step.is-done .dot      { background:#16a34a; color:#fff; }
            .op-step.is-done + .op-step::before,
            .op-step.is-active + .op-step::before { background:#16a34a; }
            .op-step.is-done .lbl      { color:#15803d; }
            .op-step.is-active .dot    { background:#fff; border-color:#16a34a; color:#16a34a; transform: scale(1.1); box-shadow: 0 0 0 6px rgba(22,163,74,.15); }
            .op-step.is-active .lbl    { color:#15803d; font-weight:700; }
            .order-progress.is-canceled { background:#fef2f2; }
            .order-progress.is-canceled .op-step .dot { background:#fee2e2; color:#b91c1c; border:none; }
            .order-progress.is-canceled .op-step .lbl { color:#7f1d1d; }
            .order-progress.is-canceled .op-step + .op-step::before { background:#fecaca; }

            /* Empty state */
            .ps-empty {
                background:#fff; border:1px dashed #cbd5e1; border-radius:14px;
                text-align:center; padding:48px 24px;
            }
            .ps-empty .ico { width:80px; height:80px; border-radius:50%; background:#f0fdf4; color:#16a34a; font-size:2.5rem; margin:0 auto 14px; display:inline-flex; align-items:center; justify-content:center; }
            .ps-empty h3 { color:#0f172a; font-weight:700; margin-bottom:6px; }
            .ps-empty p { color:#64748b; margin-bottom:18px; }

            /* Mobile */
            @media (max-width: 767.98px){
                .order-summary { grid-template-columns: auto minmax(0,1fr) 28px; gap:10px; padding:12px 14px 12px 18px; }
                .os-status, .os-total { grid-column: 2 / -2; flex-direction:row; justify-content:space-between; align-items:center; }
                .os-total { margin-top:2px; }
                .os-thumbs .thumb, .os-thumbs .more { width:40px; height:40px; }
                .order-progress { padding:14px 14px 6px; }
                .op-step .lbl { font-size:.66rem; }
                .order-line { padding:10px 14px; }
                .order-foot, .order-addr { padding-left:14px; padding-right:14px; }
                .order-addr { margin-left:14px; margin-right:14px; }
            }
        </style>
    @endpush

    {{-- HERO HEADER --}}
    <div class="ps-hero">
        <div class="d-flex align-items-center gap-3">
            <span class="ico"><i class="ti ti-receipt"></i></span>
            <div>
                <h1>Pesanan Saya</h1>
                <div class="sub">Pantau status pesanan, lanjutkan pembayaran, atau konfirmasi penerimaan barang.</div>
            </div>
        </div>
        <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-success">
            <i class="ti ti-shopping-cart me-1"></i> Belanja Lagi
        </a>
    </div>

    {{-- STATUS TABS --}}
    <div class="ps-tabs">
        @foreach ($tabs as $t)
            @php
                $url = $t['key']
                    ? request()->fullUrlWithQuery(['status' => $t['key'], 'page' => null])
                    : request()->fullUrlWithQuery(['status' => null, 'page' => null]);
                $isActive = (string) $activeStatus === (string) $t['key'];
            @endphp
            <a href="{{ $url }}" class="ps-tab {{ $isActive ? 'is-active' : '' }}">
                <i class="ti ti-{{ $t['icon'] }}"></i>
                <span>{{ $t['label'] }}</span>
                <span class="badge">{{ $t['count'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- SEARCH + SORT --}}
    <form method="GET" action="{{ route('pelanggan.pesanan.index') }}" class="ps-search-wrap">
        @if ($activeStatus)
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif
        <i class="ti ti-search"></i>
        <input type="search" name="q" placeholder="Cari nomor pesanan..." value="{{ request('q') }}">
        <select name="sort" onchange="this.form.submit()">
            @foreach ($sortOptions as $key => $label)
                <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit"><i class="ti ti-filter me-1"></i>Cari</button>
    </form>

    @forelse ($items as $order)
        @php
            $current   = $stepIndex($order->status);
            $statusKey = $order->status->value;
            $allItems  = $order->items;
            $thumbItems = $allItems->take(3);
            $moreCount  = max(0, $allItems->count() - 3);
            $firstName  = $allItems->first()?->product?->nama ?? 'Produk';
            $totalQty   = $allItems->sum('jumlah');
        @endphp
        <details class="order-item s-{{ $statusKey }}">
            <summary class="order-summary">
                {{-- Thumbnails --}}
                <div class="os-thumbs">
                    @foreach ($thumbItems as $i)
                        <img class="thumb" src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                             alt="{{ $i->product?->nama ?? 'produk' }}" loading="lazy" decoding="async">
                    @endforeach
                    @if ($moreCount > 0)
                        <span class="more">+{{ $moreCount }}</span>
                    @endif
                </div>

                {{-- Info: produk + meta --}}
                <div class="os-info">
                    <div class="product-summary">
                        @if ($allItems->count() === 1)
                            {{ $firstName }}
                        @else
                            {{ $firstName }} <span class="text-secondary fw-normal">+ {{ $allItems->count() - 1 }} lainnya</span>
                        @endif
                    </div>
                    <div class="meta">
                        <span class="code">{{ $order->code }}</span>
                        <span class="dot">·</span>
                        <span><i class="ti ti-clock me-1"></i>{{ $order->created_at->diffForHumans() }}</span>
                        <span class="dot">·</span>
                        <span>{{ $totalQty }} item</span>
                    </div>
                </div>

                {{-- Status --}}
                <div class="os-status">
                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->customerLabel() }}</span>
                    @if ($order->metode_pembayaran)
                        <span class="badge bg-secondary-lt ms-1" title="{{ $order->metode_pembayaran->label() }}">
                            <i class="ti ti-{{ $order->metode_pembayaran->icon() }} me-1"></i>{{ $order->metode_pembayaran->shortLabel() }}
                        </span>
                    @endif
                    @if ($order->status === OrderStatus::Pending && $order->expires_at && ! $order->isPaymentExpired())
                        <span class="countdown" data-pay-countdown="{{ $order->expires_at->toIso8601String() }}">
                            <i class="ti ti-clock"></i><span data-countdown-text>—</span>
                        </span>
                    @endif
                </div>

                {{-- Total --}}
                <div class="os-total">
                    <span class="lbl">Total</span>
                    <span class="val">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                </div>

                <span class="os-chev"><i class="ti ti-chevron-down"></i></span>
            </summary>

            <div class="order-body">
                {{-- Status bar --}}
                <div class="order-progress {{ $order->status === OrderStatus::Batal ? 'is-canceled' : '' }}">
                    @if ($order->status === OrderStatus::Batal)
                        @php $cancelSteps = [['x', 'Dibatalkan'], ['ban', 'Order tidak diproses']]; @endphp
                        @foreach ($cancelSteps as [$ic, $lbl])
                            <div class="op-step">
                                <span class="dot"><i class="ti ti-{{ $ic }}"></i></span>
                                <span class="lbl">{{ $lbl }}</span>
                            </div>
                        @endforeach
                    @else
                        @foreach ($steps as $idx => [$st, $label, $icon])
                            @php
                                $stepNum  = $idx + 1;
                                $isDone   = $stepNum < $current;
                                $isActive = $stepNum === $current;
                            @endphp
                            <div class="op-step {{ $isDone ? 'is-done' : '' }} {{ $isActive ? 'is-active' : '' }}">
                                <span class="dot">
                                    @if ($isDone)<i class="ti ti-check"></i>@else<i class="ti ti-{{ $icon }}"></i>@endif
                                </span>
                                <span class="lbl">{{ $label }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Daftar produk lengkap --}}
                <div>
                    @foreach ($order->items as $i)
                        <div class="order-line">
                            <img class="thumb" src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                                 alt="{{ $i->product?->nama ?? 'produk dihapus' }}" loading="lazy" decoding="async">
                            <div class="flex-grow-1 text-truncate">
                                <div class="name text-truncate">
                                    @if ($i->product){{ $i->product->nama }}@else<span class="muted">[produk dihapus]</span>@endif
                                </div>
                                <div class="qty">{{ $i->jumlah }} × Rp {{ number_format($i->harga, 0, ',', '.') }}</div>
                            </div>
                            <div class="sub">Rp {{ number_format($i->harga * $i->jumlah, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Alamat --}}
                @if ($order->alamat_pengiriman)
                    <div class="order-addr">
                        <i class="ti ti-map-pin ico"></i>
                        <div>
                            <div class="name">{{ $order->nama_penerima }} · {{ $order->no_hp_penerima }}</div>
                            <div class="text">
                                {{ $order->alamat_pengiriman }}<br>
                                {{ $order->shipping_district_name }}, {{ $order->shipping_city_name }}, {{ $order->shipping_province_name }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Footer aksi --}}
                <div class="order-foot">
                    <div class="status-msg">
                        @if ($order->status === OrderStatus::Pending && $order->isPaymentExpired())
                            <i class="ti ti-clock-x text-danger"></i>
                            <span class="text-danger">Batas waktu pembayaran terlewat</span>
                        @elseif ($order->status === OrderStatus::Pending)
                            <i class="ti ti-clock text-warning"></i>
                            <span>Selesaikan pembayaran sebelum batas waktu</span>
                        @elseif ($order->status === OrderStatus::Dibayar)
                            <i class="ti ti-package text-info"></i>
                            <span>Penjual sedang menyiapkan pesanan Anda</span>
                        @elseif ($order->status === OrderStatus::Dikirim)
                            <i class="ti ti-truck-delivery text-primary"></i>
                            <span>Pesanan dalam perjalanan — konfirmasi setelah diterima</span>
                        @elseif ($order->status === OrderStatus::Selesai)
                            <i class="ti ti-circle-check text-success"></i>
                            <span>Pesanan telah diterima</span>
                        @elseif ($order->status === OrderStatus::Batal)
                            <i class="ti ti-ban text-danger"></i>
                            <span>Pesanan dibatalkan</span>
                        @endif
                    </div>
                    <div class="actions">
                        @if (in_array($order->status, [OrderStatus::Dibayar, OrderStatus::Dikirim, OrderStatus::Selesai], true))
                            <a href="{{ route('pelanggan.pesanan.invoice', $order) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="ti ti-receipt me-1"></i> Cetak Invoice
                            </a>
                        @endif
                        @if ($order->status === OrderStatus::Pending && $order->metode_pembayaran?->usesMidtrans() && ! $order->isPaymentExpired())
                            @if ($order->expires_at)
                                <span class="countdown-tag" data-pay-countdown="{{ $order->expires_at->toIso8601String() }}">
                                    <i class="ti ti-clock"></i><span data-countdown-text>—</span>
                                </span>
                            @endif
                            <form action="{{ route('pelanggan.pembayaran.sync', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-refresh me-1"></i> Cek Status
                                </button>
                            </form>
                            <a href="{{ route('pelanggan.pembayaran.show', $order) }}" class="btn btn-success btn-sm">
                                <i class="ti ti-credit-card me-1"></i> Bayar Sekarang
                            </a>
                        @elseif ($order->status === OrderStatus::Dikirim)
                            <form action="{{ route('pelanggan.pesanan.diterima', $order) }}" method="POST" class="d-inline"
                                  data-confirm="Pastikan barang sudah Anda terima dan dalam kondisi baik. Konfirmasi ini tidak dapat dibatalkan."
                                  data-confirm-title="Konfirmasi Pesanan Diterima?"
                                  data-confirm-icon="success"
                                  data-confirm-button="Ya, sudah saya terima">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="ti ti-package-import me-1"></i> Pesanan Diterima
                                </button>
                            </form>
                        @elseif ($order->status === OrderStatus::Selesai)
                            <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-outline-success btn-sm">
                                <i class="ti ti-shopping-cart me-1"></i> Beli Lagi
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </details>
    @empty
        <div class="ps-empty">
            <span class="ico"><i class="ti ti-shopping-bag"></i></span>
            <h3>
                @if ($activeStatus)
                    Tidak ada pesanan {{ collect($tabs)->firstWhere('key', $activeStatus)['label'] ?? '' }}
                @else
                    Belum ada pesanan
                @endif
            </h3>
            <p>
                @if ($activeStatus)
                    Coba pilih filter status lain atau lihat semua pesanan Anda.
                @else
                    Yuk mulai belanja produk segar dari petani lokal!
                @endif
            </p>
            @if ($activeStatus)
                <a href="{{ route('pelanggan.pesanan.index') }}" class="btn btn-outline-success">
                    <i class="ti ti-list me-1"></i> Lihat Semua
                </a>
            @else
                <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-success">
                    <i class="ti ti-shopping-cart me-1"></i> Mulai Belanja
                </a>
            @endif
        </div>
    @endforelse

    @if ($items->hasPages())
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $items->total() }}</strong>
            </div>
            {{ $items->links() }}
        </div>
    @endif

    @push('scripts')
        <script>
            // Live countdown untuk order Pending — refresh halaman saat habis
            // supaya status server-side (Batal) ter-render ulang.
            (function () {
                const els = document.querySelectorAll('[data-pay-countdown]');
                if (! els.length) return;

                function pad(n) { return String(n).padStart(2, '0'); }

                function tick() {
                    let anyExpired = false;
                    els.forEach(el => {
                        const target = new Date(el.dataset.payCountdown).getTime();
                        const diff   = target - Date.now();
                        const txt    = el.querySelector('[data-countdown-text]');
                        if (! txt) return;
                        if (diff <= 0) {
                            txt.textContent = 'Kedaluwarsa';
                            el.classList.add('is-warn');
                            anyExpired = true;
                            return;
                        }
                        const m = Math.floor(diff / 60000);
                        const s = Math.floor((diff % 60000) / 1000);
                        txt.textContent = pad(m) + ':' + pad(s);
                        if (diff < 60000) el.classList.add('is-warn');
                    });
                    if (anyExpired) setTimeout(() => window.location.reload(), 1500);
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endpush
</x-layouts.storefront>
