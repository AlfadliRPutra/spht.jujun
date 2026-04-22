@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Pesanan Masuk';
    $active = 'petani.pesanan';
@endphp

<x-layouts.app :title="$title" :active="$active">
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
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
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
