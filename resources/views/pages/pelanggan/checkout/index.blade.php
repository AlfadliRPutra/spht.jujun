@php
    $title  = 'Checkout';
    $active = 'pelanggan.keranjang';
    $cart   = auth()->user()->cart()->with('items.product')->first();
    $items  = $cart?->items ?? collect();
    $total  = $items->sum(fn ($i) => $i->product->harga * $i->jumlah);
@endphp

<x-layouts.storefront :title="$title" :active="$active">
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
                    <input type="hidden" name="metode" value="midtrans">
                    <div class="d-flex align-items-center gap-3 p-3 rounded border" style="background:#f8fafc">
                        <div class="avatar avatar-md bg-primary-lt rounded">
                            <i class="ti ti-credit-card" style="font-size:1.6rem"></i>
                        </div>
                        <div class="flex-fill">
                            <div class="fw-semibold">Pembayaran via Midtrans</div>
                            <div class="text-secondary small">Virtual Account, QRIS, E-Wallet, Kartu Kredit &mdash; pilih di halaman berikutnya.</div>
                        </div>
                        <span class="badge bg-green-lt"><i class="ti ti-shield-check me-1"></i>Aman</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Ringkasan Pesanan</h3></div>
                <div class="card-body">
                    @foreach ($items as $item)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->nama }}" class="rounded" style="width:44px;height:44px;object-fit:cover" loading="lazy" decoding="async">
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
                    <button class="btn btn-success w-100">
                        <i class="ti ti-credit-card me-1"></i> Bayar via Midtrans
                    </button>
                </div>
            </div>
        </div>
    </form>
</x-layouts.storefront>
