@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Pesanan Saya';
    $active = 'pelanggan.pesanan';
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    <div class="card">
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

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Tanggal</th>
                        <th class="text-end">Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($order->metode_pembayaran ?? '-') }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                            <td><a href="#" class="btn btn-sm btn-outline-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan.</td></tr>
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
</x-layouts.storefront>
