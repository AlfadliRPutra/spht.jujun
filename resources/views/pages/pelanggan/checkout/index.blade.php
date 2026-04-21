@php
    $title  = 'Checkout';
    $active = 'pelanggan.keranjang';
    $cart   = auth()->user()->cart()->with('items.product')->first();
    $items  = $cart?->items ?? collect();
    $total  = $items->sum(fn ($i) => $i->product->harga * $i->jumlah);
    $metodePembayaran = [
        'transfer' => 'Transfer Bank',
        'ewallet'  => 'E-Wallet',
        'cod'      => 'Bayar di Tempat (COD)',
    ];
@endphp

<x-layouts.app :title="$title" :active="$active">
    <form action="{{ route('pelanggan.pembayaran.index') }}" method="GET" class="row row-cards">
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Alamat Pengiriman</h3></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->name }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->no_hp }}">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea class="form-control" rows="3">{{ auth()->user()->alamat }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Metode Pembayaran</h3></div>
                <div class="card-body">
                    @foreach ($metodePembayaran as $value => $label)
                        <label class="form-selectgroup-item d-block mb-2">
                            <input type="radio" name="metode" value="{{ $value }}" class="form-selectgroup-input" @checked($loop->first)>
                            <span class="form-selectgroup-label d-flex align-items-center p-3">
                                <span class="form-selectgroup-label-content">{{ $label }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ringkasan Pesanan</h3></div>
                <div class="card-body">
                    @foreach ($items as $item)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->nama }}" class="rounded" style="width:44px;height:44px;object-fit:cover">
                            <div class="flex-fill">
                                <div class="small fw-semibold text-truncate">{{ $item->product->nama }}</div>
                                <div class="text-secondary small">× {{ $item->jumlah }}</div>
                            </div>
                            <div class="small">Rp {{ number_format($item->product->harga * $item->jumlah, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between h4">
                        <span>Total</span>
                        <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100">Lanjut ke Pembayaran</button>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>
