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
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            /* Single list-item per order. Klik header untuk expand. */
            .order-item {
                background:#fff; border:1px solid var(--spht-border); border-radius: var(--spht-radius);
                margin-bottom:.6rem; overflow:hidden;
                transition: border-color .15s ease, box-shadow .15s ease;
            }
            .order-item:hover { border-color:#cbd5e1; }
            .order-item[open] { box-shadow: 0 .5rem 1rem rgba(15,23,42,.06); border-color:#cbd5e1; }

            /* <summary> = baris ringkas. Buang marker bawaan browser. */
            .order-summary {
                display:grid; align-items:center; gap:.85rem;
                grid-template-columns: minmax(0,1.4fr) auto minmax(120px,auto) 28px;
                padding:.75rem 1rem; cursor:pointer;
                list-style:none; user-select:none;
            }
            .order-summary::-webkit-details-marker { display:none; }
            .order-summary::marker { content:''; }
            .order-summary:hover { background:#f8fafc; }

            .os-meta { min-width:0; }
            .os-meta .code { font-family:'SF Mono','Menlo',monospace; font-size:.88rem; font-weight:600; color:#0f172a; letter-spacing:.02em; }
            .os-meta .date { font-size:.75rem; color:var(--spht-muted); margin-top:.1rem; }

            .os-total { font-weight:700; color:var(--spht-green-dark, #15803d); white-space:nowrap; text-align:right; font-size:.95rem; }
            .os-total .lbl { display:block; font-size:.7rem; color:var(--spht-muted); font-weight:500; }

            .os-chev {
                width:28px; height:28px; border-radius:50%;
                display:inline-flex; align-items:center; justify-content:center;
                background:#f1f5f9; color:#475569;
                transition: transform .2s ease, background .15s;
            }
            .order-item[open] .os-chev { transform: rotate(180deg); background: var(--brand-50, #ecfdf5); color: var(--spht-green-dark, #15803d); }

            /* Body yang muncul saat di-expand */
            .order-body { border-top:1px solid #eef2f7; }

            /* Product list rows */
            .order-line { display:flex; align-items:center; gap:.85rem; padding:.65rem 1.1rem; }
            .order-line + .order-line { border-top:1px dashed #eef2f7; }
            .order-line .thumb { width:48px; height:48px; border-radius:.55rem; object-fit:cover; background:#f6f8fa; border:1px solid #e5e7eb; flex-shrink:0; }
            .order-line .name { font-weight:500; color:#1f2937; }
            .order-line .muted { color:#94a3b8; font-style:italic; }
            .order-line .qty  { color:var(--spht-muted); font-size:.85rem; }
            .order-line .sub  { font-weight:600; color:#0f172a; white-space:nowrap; }

            /* Footer: total + tombol aksi */
            .order-foot { display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding:.8rem 1.1rem; background:#fafbfc; border-top:1px solid var(--spht-border); flex-wrap:wrap; }

            /* Method chip */
            .method-chip { display:inline-flex; align-items:center; gap:.25rem; padding:.18rem .55rem; border-radius:999px; background:#eef2f7; color:#334155; font-size:.7rem; text-transform:uppercase; letter-spacing:.03em; font-weight:600; }

            /* Status bar / stepper */
            .order-progress { display:flex; align-items:flex-start; gap:0; padding:1rem 1.1rem .65rem; background:#fff; }
            .op-step { flex:1; display:flex; flex-direction:column; align-items:center; gap:.4rem; position:relative; min-width:0; }
            .op-step .dot {
                width:34px; height:34px; border-radius:50%;
                display:inline-flex; align-items:center; justify-content:center;
                background:#e2e8f0; color:#94a3b8;
                border:2px solid transparent; font-size:1rem;
                transition: background .2s, color .2s, border-color .2s, transform .2s;
                z-index:1; flex-shrink:0;
            }
            .op-step .lbl { font-size:.74rem; color:#94a3b8; font-weight:600; text-align:center; line-height:1.2; }
            .op-step + .op-step::before {
                content:""; position:absolute; left:-50%; top:16px;
                width:100%; height:3px; background:#e2e8f0; z-index:0;
            }
            .op-step.is-done .dot      { background:var(--spht-green, #16a34a); color:#fff; }
            .op-step.is-done + .op-step::before,
            .op-step.is-active + .op-step::before { background:var(--spht-green, #16a34a); }
            .op-step.is-done .lbl      { color:#0f172a; }
            .op-step.is-active .dot    { background:#fff; border-color:var(--spht-green, #16a34a); color:var(--spht-green, #16a34a); transform: scale(1.06); box-shadow: 0 0 0 6px rgba(22,163,74,.12); }
            .op-step.is-active .lbl    { color:var(--spht-green-dark, #15803d); font-weight:700; }
            .order-progress.is-canceled { background:#fef2f2; }
            .order-progress.is-canceled .op-step .dot { background:#fee2e2; color:#b91c1c; border:none; }
            .order-progress.is-canceled .op-step .lbl { color:#7f1d1d; }
            .order-progress.is-canceled .op-step + .op-step::before { background:#fecaca; }

            /* Mobile: lebih ringkas */
            @media (max-width: 575.98px){
                .order-summary { grid-template-columns: 1fr auto 24px; gap:.5rem; }
                .os-status { grid-column: 1 / -1; }
                .op-step .lbl { font-size:.66rem; }
            }
        </style>
    @endpush

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pesanan ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('pelanggan.pesanan.index')"
            placeholder="Cari nomor order..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Status</label>
                    <select name="status" class="form-select" style="min-width:170px">
                        <option value="">Semua</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->customerLabel() }}</option>
                        @endforeach
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>
    </div>

    @forelse ($items as $order)
        @php $current = $stepIndex($order->status); @endphp
        <details class="order-item">
            <summary class="order-summary">
                <div class="os-meta">
                    <div class="code">{{ $order->code }}</div>
                    <div class="date"><i class="ti ti-calendar me-1"></i>{{ $order->created_at->translatedFormat('d M Y · H:i') }}</div>
                </div>
                <div class="os-status">
                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->customerLabel() }}</span>
                </div>
                <div class="os-total">
                    <span class="lbl">Total</span>
                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
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
                                    @if ($isDone)
                                        <i class="ti ti-check"></i>
                                    @else
                                        <i class="ti ti-{{ $icon }}"></i>
                                    @endif
                                </span>
                                <span class="lbl">{{ $label }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>

                {{-- Method chip --}}
                @if ($order->metode_pembayaran)
                    <div class="px-3 pt-2 pb-1">
                        <span class="method-chip">
                            <i class="ti ti-{{ match($order->metode_pembayaran) {
                                'transfer' => 'building-bank',
                                'ewallet'  => 'wallet',
                                'cod'      => 'cash',
                                default    => 'credit-card'
                            } }}"></i>
                            {{ $order->metode_pembayaran }}
                        </span>
                    </div>
                @endif

                {{-- Daftar produk --}}
                <div>
                    @foreach ($order->items as $i)
                        <div class="order-line">
                            <img class="thumb" src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                                 alt="{{ $i->product?->nama ?? 'produk dihapus' }}" loading="lazy" decoding="async">
                            <div class="flex-grow-1 text-truncate">
                                <div class="name text-truncate">
                                    @if ($i->product)
                                        {{ $i->product->nama }}
                                    @else
                                        <span class="muted">[produk dihapus]</span>
                                    @endif
                                </div>
                                <div class="qty">{{ $i->jumlah }} × Rp {{ number_format($i->harga, 0, ',', '.') }}</div>
                            </div>
                            <div class="sub">Rp {{ number_format($i->harga * $i->jumlah, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer aksi --}}
                <div class="order-foot">
                    <div class="text-secondary small">
                        @if ($order->status === OrderStatus::Pending && $order->isPaymentExpired())
                            <span class="text-danger"><i class="ti ti-clock-x me-1"></i>Batas waktu pembayaran terlewat</span>
                        @elseif ($order->status === OrderStatus::Selesai)
                            <i class="ti ti-circle-check text-success me-1"></i>Pesanan telah diterima
                        @elseif ($order->status === OrderStatus::Dikirim)
                            <i class="ti ti-truck-delivery text-primary me-1"></i>Pesanan sedang dalam pengiriman
                        @elseif ($order->status === OrderStatus::Dibayar)
                            <i class="ti ti-package text-info me-1"></i>Penjual sedang menyiapkan pesanan
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        @if ($order->status === OrderStatus::Pending && $order->metode_pembayaran === 'midtrans' && ! $order->isPaymentExpired())
                            @if ($order->expires_at)
                                <span class="text-secondary small d-inline-flex align-items-center" data-pay-countdown="{{ $order->expires_at->toIso8601String() }}">
                                    <i class="ti ti-clock me-1"></i><span data-countdown-text>—</span>
                                </span>
                            @endif
                            <form action="{{ route('pelanggan.pembayaran.sync', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-refresh me-1"></i> Cek Status
                                </button>
                            </form>
                            <a href="{{ route('pelanggan.pembayaran.show', $order) }}" class="btn btn-success btn-sm">
                                <i class="ti ti-credit-card me-1"></i> Lanjutkan Bayar
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
                        @endif
                    </div>
                </div>
            </div>
        </details>
    @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ti ti-shopping-bag mb-2" style="font-size:2.5rem;color:#cbd5e1"></i>
                <div class="text-secondary mb-3">Belum ada pesanan.</div>
                <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-success">
                    <i class="ti ti-shopping-cart me-1"></i> Mulai Belanja
                </a>
            </div>
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
                            el.classList.add('text-danger');
                            anyExpired = true;
                            return;
                        }
                        const m = Math.floor(diff / 60000);
                        const s = Math.floor((diff % 60000) / 1000);
                        txt.textContent = pad(m) + ':' + pad(s);
                        if (diff < 60000) el.classList.add('text-danger');
                    });
                    if (anyExpired) setTimeout(() => window.location.reload(), 1500);
                }
                tick();
                setInterval(tick, 1000);
            })();
        </script>
    @endpush
</x-layouts.storefront>
