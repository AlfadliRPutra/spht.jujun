@php
    $title   = 'Pesanan Masuk';
    $active  = 'petani.pesanan';
    $pesanan = \App\Models\OrderItem::with(['order.user', 'product'])
        ->whereHas('product', fn ($q) => $q->where('user_id', auth()->id()))
        ->latest('id')
        ->get()
        ->groupBy('order_id');
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pesanan dari Pelanggan</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $orderId => $items)
                        @php($order = $items->first()->order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $items->pluck('product.nama')->implode(', ') }}</td>
                            <td class="text-end">Rp {{ number_format($items->sum(fn ($i) => $i->harga * $i->jumlah), 0, ',', '.') }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
