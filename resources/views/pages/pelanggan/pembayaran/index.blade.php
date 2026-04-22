@php
    $title  = 'Pembayaran';
    $active = 'pelanggan.pesanan';
    $cart   = auth()->user()->cart()->with('items.product')->first();
    $items  = $cart?->items ?? collect();
    $total  = $items->sum(fn ($i) => $i->product->harga * $i->jumlah);
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pembayaran</h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                         style="width:72px;height:72px;background:#e0f2fe;color:#0284c7">
                        <i class="ti ti-credit-card" style="font-size:2rem"></i>
                    </div>

                    <h2 class="mb-1">Bayar via Midtrans</h2>
                    <p class="text-secondary mb-4">
                        Anda akan diarahkan ke halaman Midtrans untuk memilih metode (VA, QRIS, E-Wallet, Kartu Kredit).
                    </p>

                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded mb-4"
                         style="background:#f8fafc;border:1px solid #e2e8f0">
                        <span class="text-secondary">Total Bayar</span>
                        <span class="h3 mb-0 text-success">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <button type="button" class="btn btn-success btn-lg" disabled>
                            <i class="ti ti-external-link me-1"></i> Lanjut ke Midtrans
                        </button>
                        <a href="{{ route('pelanggan.keranjang.index') }}" class="btn btn-link text-secondary">Kembali ke Keranjang</a>
                    </div>

                    <div class="alert alert-warning mt-4 mb-0 text-start">
                        <i class="ti ti-info-circle me-1"></i>
                        Integrasi Midtrans belum diaktifkan. Tombol di atas akan aktif setelah credential Midtrans diisi di konfigurasi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.storefront>
