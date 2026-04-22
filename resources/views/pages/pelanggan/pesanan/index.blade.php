@php
    $title   = 'Pesanan Saya';
    $active  = 'pelanggan.pesanan';
    $pesanan = auth()->user()->orders()->latest()->get();
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Pesanan</h3>
        </div>
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
                    @forelse ($pesanan as $order)
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
    </div>
</x-layouts.storefront>
