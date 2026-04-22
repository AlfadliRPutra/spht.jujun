@php
    $title  = 'Keranjang';
    $active = 'pelanggan.keranjang';
    $cart   = auth()->user()->cart()->with('items.product')->first();
    $items  = $cart?->items ?? collect();
    $total  = $items->sum(fn ($i) => $i->product->harga * $i->jumlah);
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Keranjang Belanja</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th class="text-end">Harga</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->nama }}" class="rounded" style="width:56px;height:56px;object-fit:cover">
                                    <div>
                                        <div class="fw-semibold">{{ $item->product->nama }}</div>
                                        <div class="text-secondary small">{{ $item->product->category?->nama }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">Rp {{ number_format($item->product->harga, 0, ',', '.') }}</td>
                            <td class="text-center" style="width:120px">
                                <input type="number" class="form-control form-control-sm" value="{{ $item->jumlah }}" min="1">
                            </td>
                            <td class="text-end">Rp {{ number_format($item->product->harga * $item->jumlah, 0, ',', '.') }}</td>
                            <td><button class="btn btn-sm btn-outline-danger">Hapus</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary py-4">Keranjang kosong.</td></tr>
                    @endforelse
                </tbody>
                @if ($items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">Rp {{ number_format($total, 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($items->isNotEmpty())
            <div class="card-footer text-end">
                <a href="{{ route('pelanggan.checkout.index') }}" class="btn btn-primary">Checkout</a>
            </div>
        @endif
    </div>
</x-layouts.storefront>
