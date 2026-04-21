@php
    $title   = 'Laporan';
    $active  = 'petani.laporan';
    $laporan = \App\Models\OrderItem::with(['order', 'product'])
        ->whereHas('product', fn ($q) => $q->where('user_id', auth()->id()))
        ->whereHas('order', fn ($q) => $q->where('status', \App\Enums\OrderStatus::Selesai))
        ->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Laporan Penjualan</h3>
            <button onclick="window.print()" class="btn btn-primary">Cetak</button>
        </div>
        <div class="card-body">
            <form class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Dari</label>
                    <input type="date" name="dari" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sampai</label>
                    <input type="date" name="sampai" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary">Terapkan</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-vcenter">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($laporan as $item)
                            <tr>
                                <td>{{ $item->order->created_at->format('d/m/Y') }}</td>
                                <td>{{ $item->product->nama }}</td>
                                <td class="text-end">{{ $item->jumlah }}</td>
                                <td class="text-end">Rp {{ number_format($item->harga * $item->jumlah, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">Rp {{ number_format($laporan->sum(fn ($i) => $i->harga * $i->jumlah), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
