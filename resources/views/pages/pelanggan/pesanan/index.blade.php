@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Pesanan Saya';
    $active = 'pelanggan.pesanan';
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            .order-card { background:#fff; border:1px solid var(--spht-border); border-radius: var(--spht-radius); overflow:hidden; transition: border-color .15s ease, box-shadow .15s ease; }
            .order-card:hover { border-color:#cbd5e1; box-shadow: 0 .5rem 1rem rgba(15,23,42,.04); }
            .order-card-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.85rem 1.1rem; background:#f8fafc; border-bottom:1px solid var(--spht-border); flex-wrap:wrap; }
            .order-card-head .left { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
            .order-card-head .right { display:flex; align-items:center; gap:.6rem; color:var(--spht-muted); font-size:.82rem; flex-wrap:wrap; }
            .order-code { font-family:'SF Mono','Menlo',monospace; font-size:.88rem; font-weight:600; color:#0f172a; letter-spacing:.02em; }
            .order-date { font-size:.78rem; color:var(--spht-muted); display:block; margin-top:.15rem; }
            .method-chip { display:inline-flex; align-items:center; gap:.25rem; padding:.18rem .55rem; border-radius:999px; background:#eef2f7; color:#334155; font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; font-weight:600; }

            .order-line { display:flex; align-items:center; gap:.85rem; padding:.65rem 1.1rem; }
            .order-line + .order-line { border-top:1px dashed #eef2f7; }
            .order-line .thumb { width:52px; height:52px; border-radius:.55rem; object-fit:cover; background:#f6f8fa; border:1px solid #e5e7eb; flex-shrink:0; }
            .order-line .name { font-weight:500; color:#1f2937; }
            .order-line .muted { color:#94a3b8; font-style:italic; }
            .order-line .qty  { color:var(--spht-muted); font-size:.85rem; }
            .order-line .sub  { font-weight:600; color:#0f172a; white-space:nowrap; }

            .order-card-foot { display:flex; justify-content:space-between; align-items:center; gap:.75rem; padding:.8rem 1.1rem; background:#fafbfc; border-top:1px solid var(--spht-border); flex-wrap:wrap; }
            .order-total { font-size:.78rem; color:var(--spht-muted); }
            .order-total strong { display:block; font-size:1.1rem; color:var(--spht-green-dark); }
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
                            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>
    </div>

    @forelse ($items as $order)
        <div class="order-card mb-3">
            <div class="order-card-head">
                <div class="left">
                    <div>
                        <div class="order-code">{{ $order->code }}</div>
                        <span class="order-date"><i class="ti ti-calendar me-1"></i>{{ $order->created_at->format('d M Y · H:i') }}</span>
                    </div>
                    <span class="badge {{ $order->status->badgeClass() }} ms-2">{{ $order->status->label() }}</span>
                </div>
                <div class="right">
                    @if ($order->metode_pembayaran)
                        <span class="method-chip">
                            <i class="ti ti-{{ match($order->metode_pembayaran) {
                                'transfer' => 'building-bank',
                                'ewallet'  => 'wallet',
                                'cod'      => 'cash',
                                default    => 'credit-card'
                            } }}"></i>
                            {{ $order->metode_pembayaran }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="order-card-body">
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

            <div class="order-card-foot">
                <div class="order-total">
                    Total Pesanan
                    <strong>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</strong>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm"
                        data-bs-toggle="modal" data-bs-target="#detail-order-{{ $order->id }}">
                    Detail <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
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

    @foreach ($items as $order)
        <div class="modal modal-blur fade" id="detail-order-{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">Detail Pesanan</h5>
                            <div class="text-secondary small">{{ $order->code }} &middot; {{ $order->created_at->format('d M Y · H:i') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                            @if ($order->metode_pembayaran)
                                <span class="badge bg-secondary-lt"><i class="ti ti-credit-card me-1"></i>{{ ucfirst($order->metode_pembayaran) }}</span>
                            @endif
                        </div>

                        <h6 class="text-uppercase text-secondary small fw-bold mb-2">Produk</h6>
                        <div class="border rounded mb-3">
                            @foreach ($order->items as $i)
                                <div class="d-flex align-items-center gap-3 p-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    <img src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                                         alt="" style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;background:#f6f8fa"
                                         loading="lazy">
                                    <div class="flex-fill">
                                        <div class="fw-medium">
                                            @if ($i->product)
                                                {{ $i->product->nama }}
                                            @else
                                                <span class="text-secondary fst-italic">[produk dihapus]</span>
                                            @endif
                                        </div>
                                        <div class="text-secondary small">{{ $i->jumlah }} × Rp {{ number_format($i->harga, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="fw-semibold">Rp {{ number_format($i->harga * $i->jumlah, 0, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <h6 class="text-uppercase text-secondary small fw-bold mb-2">Alamat Pengiriman</h6>
                                <div class="border rounded p-3 small">
                                    <div class="fw-medium">{{ $order->user->name }}</div>
                                    <div class="text-secondary">{{ $order->user->no_hp ?? '—' }}</div>
                                    <div class="text-secondary mt-1">{{ $order->user->alamat ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-uppercase text-secondary small fw-bold mb-2">Ringkasan</h6>
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-secondary">Subtotal</span>
                                        <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-secondary">Ongkir</span>
                                        <span>Rp 0</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total</span>
                                        <span class="text-success">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Tutup</button>
                        @if ($order->status->value === 'pending')
                            <a href="{{ route('pelanggan.pembayaran.index') }}" class="btn btn-success">
                                <i class="ti ti-credit-card me-1"></i> Bayar Sekarang
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-layouts.storefront>
