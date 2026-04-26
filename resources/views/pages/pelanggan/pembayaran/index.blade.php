@php
    $title  = 'Pembayaran';
    $active = 'pelanggan.pesanan';
    $items  = $order->items;
    $total  = (float) $order->total_harga;
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pembayaran — {{ $order->code }}</h3>
                </div>
                <div class="card-body text-center py-5">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                         style="width:72px;height:72px;background:#e0f2fe;color:#0284c7">
                        <i class="ti ti-credit-card" style="font-size:2rem"></i>
                    </div>

                    <h2 class="mb-1">Bayar via Midtrans</h2>
                    <p class="text-secondary mb-4">
                        Klik tombol di bawah untuk memilih metode (VA, QRIS, E-Wallet, Kartu Kredit).
                    </p>

                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded mb-4"
                         style="background:#f8fafc;border:1px solid #e2e8f0">
                        <span class="text-secondary">Total Bayar</span>
                        <span class="h3 mb-0 text-success">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <button id="pay-button" type="button" class="btn btn-success btn-lg" @if (! $order->snap_token) disabled @endif>
                            <i class="ti ti-external-link me-1"></i> Lanjut ke Midtrans
                        </button>
                        <a href="{{ route('pelanggan.pesanan.index') }}" class="btn btn-link text-secondary">Lihat Daftar Pesanan</a>
                    </div>

                    @if (! $order->snap_token)
                        <div class="alert alert-warning mt-4 mb-0 text-start">
                            <i class="ti ti-info-circle me-1"></i>
                            Token pembayaran belum tersedia. Silakan muat ulang halaman ini.
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="text-secondary small">
                        <div class="mb-1"><strong>Penerima:</strong> {{ $order->nama_penerima }} &middot; {{ $order->no_hp_penerima }}</div>
                        <div><strong>Alamat:</strong> {{ $order->alamat_pengiriman }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://app.{{ $production ? '' : 'sandbox.' }}midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
        <script>
            (function () {
                var btn   = document.getElementById('pay-button');
                var token = @json($order->snap_token);
                if (! btn || ! token) return;

                var finishUrl   = @json(route('pelanggan.pembayaran.finish', $order));
                var unfinishUrl = @json(route('pelanggan.pembayaran.unfinish', $order));
                var errorUrl    = @json(route('pelanggan.pembayaran.error', $order));

                function openSnap() {
                    if (typeof window.snap === 'undefined') {
                        alert('Snap belum siap. Coba lagi sebentar.');
                        return;
                    }
                    window.snap.pay(token, {
                        onSuccess: function () { window.location.href = finishUrl; },
                        onPending: function () { window.location.href = unfinishUrl; },
                        onError:   function () { window.location.href = errorUrl; },
                        onClose:   function () { /* user closed the popup */ }
                    });
                }

                btn.addEventListener('click', openSnap);

                window.addEventListener('load', function () {
                    setTimeout(openSnap, 400);
                });
            })();
        </script>
    @endpush
</x-layouts.storefront>
