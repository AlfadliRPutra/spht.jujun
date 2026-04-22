@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var int $petaniId */
    use App\Enums\OrderStatus;
    $title  = 'Riwayat Transaksi';
    $active = 'petani.laporan';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @push('styles')
        <style>
            .order-chip { display: inline-flex; align-items: center; gap:.25rem; padding:.25rem .55rem; border-radius:.5rem; background:#f1f5f9; color:#0f172a; font-family:'SF Mono','Menlo',monospace; font-size:.85rem; font-weight:600; }
            .product-stack { display:flex; align-items:center; }
            .product-stack .thumb { width:38px; height:38px; border-radius:.5rem; border:2px solid #fff; box-shadow:0 0 0 1px #e5e7eb; object-fit:cover; background:#f6f8fa; margin-left:-10px; }
            .product-stack .thumb:first-child { margin-left:0; }
            .product-stack .more { width:38px; height:38px; border-radius:.5rem; border:2px solid #fff; box-shadow:0 0 0 1px #e5e7eb; background:#f1f5f9; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:600; color:#475569; margin-left:-10px; }
            .product-list { font-size:.85rem; color:#334155; line-height:1.35; }
            .product-list .muted { color:#94a3b8; font-style:italic; }
            .cell-date .abs { font-size:.85rem; color:#0f172a; }
            .cell-date .rel { font-size:.72rem; color:#94a3b8; }
            .subtotal-val { font-weight:700; color:#0f172a; }
        </style>
    @endpush
    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="subheader">Total Transaksi</div>
                    <div class="h1 mb-0 mt-1">{{ number_format($totalTransaksi) }}</div>
                    <div class="text-secondary small">semua status</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="subheader">Pendapatan Selesai</div>
                    <div class="h1 mb-0 mt-1 text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                    <div class="text-secondary small">dari pesanan yang sudah selesai</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="subheader">Produk Terjual</div>
                    <div class="h1 mb-0 mt-1">{{ number_format($totalTerjual) }}</div>
                    <div class="text-secondary small">unit dari pesanan selesai</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="subheader mb-2">Ringkasan Status</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach ($statuses as $s)
                            @php($count = $statusCounts[$s->value] ?? 0)
                            <span class="badge {{ $s->badgeClass() }}" title="{{ $s->label() }}">
                                {{ $s->label() }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Transaksi ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('petani.laporan.index')"
            placeholder="Cari #order atau nama pelanggan..."
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
                <div>
                    <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div>
                    <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Subtotal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $order)
                        @php
                            $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
                            $subtotal = $ownItems->sum(fn ($i) => $i->harga * $i->jumlah);
                            $qty      = $ownItems->sum('jumlah');
                            $shown    = $ownItems->take(3);
                            $more     = max(0, $ownItems->count() - 3);
                        @endphp
                        <tr>
                            <td><span class="order-chip">{{ $order->code }}</span></td>
                            <td class="cell-date">
                                <div class="abs">{{ $order->created_at->format('d M Y') }}</div>
                                <div class="rel">{{ $order->created_at->format('H:i') }} &middot; {{ $order->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-xs rounded-circle bg-primary-lt">{{ mb_substr($order->user->name, 0, 1) }}</span>
                                    <span class="small">{{ $order->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="product-stack">
                                        @foreach ($shown as $i)
                                            <img class="thumb" src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                                                 alt="{{ $i->product?->nama ?? 'produk dihapus' }}" loading="lazy" decoding="async">
                                        @endforeach
                                        @if ($more > 0)
                                            <span class="more">+{{ $more }}</span>
                                        @endif
                                    </div>
                                    <div class="product-list text-truncate" style="max-width:220px">
                                        @foreach ($ownItems as $i)
                                            <div class="text-truncate">
                                                @if ($i->product)
                                                    {{ $i->product->nama }} <span class="text-secondary">× {{ $i->jumlah }}</span>
                                                @else
                                                    <span class="muted">[produk dihapus]</span> × {{ $i->jumlah }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">{{ $qty }}</td>
                            <td class="text-end subtotal-val">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-5">
                            <i class="ti ti-receipt-off mb-2" style="font-size:2rem;color:#cbd5e1"></i>
                            <div>Belum ada transaksi.</div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $items->total() }}</strong>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</x-layouts.app>
