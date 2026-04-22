@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var int $petaniId */
    use App\Enums\OrderStatus;
    $title  = 'Pesanan Masuk';
    $active = 'petani.pesanan';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pesanan dari Pelanggan ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('petani.pesanan.index')"
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
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Tanggal</th>
                        <th class="text-end">Subtotal</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $order)
                        @php
                            $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
                            $subtotal = $ownItems->sum(fn ($i) => $i->harga * $i->jumlah);
                        @endphp
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="text-truncate" style="max-width:260px">{{ $ownItems->pluck('product.nama')->implode(', ') }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                            <td class="text-nowrap">
                                @if ($order->status === OrderStatus::Dibayar)
                                    <form action="{{ route('petani.pesanan.ship', $order) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Tandai pesanan #{{ $order->id }} sebagai dikirim?');">
                                        @csrf
                                        <button class="btn btn-sm btn-primary"><i class="ti ti-truck-delivery me-1"></i> Kirim</button>
                                    </form>
                                @elseif ($order->status === OrderStatus::Dikirim)
                                    <form action="{{ route('petani.pesanan.complete', $order) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Selesaikan pesanan #{{ $order->id }}?');">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="ti ti-circle-check me-1"></i> Selesai</button>
                                    </form>
                                @endif

                                @if (! in_array($order->status, [OrderStatus::Selesai, OrderStatus::Batal], true))
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#cancel-{{ $order->id }}">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal modal-blur fade" id="cancel-{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('petani.pesanan.cancel', $order) }}" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Batalkan Pesanan #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label">Alasan pembatalan (wajib)</label>
                                                    <textarea name="cancel_reason" rows="3" class="form-control"
                                                              placeholder="mis. Stok habis, tidak sesuai pesanan, dll." required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-danger">Batalkan Pesanan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada pesanan.</td></tr>
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
