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

                    <h2 class="mb-1">Selesaikan Pembayaran</h2>
                    <p class="text-secondary mb-4">
                        Klik tombol di bawah untuk memilih metode (VA, QRIS, E-Wallet, Kartu Kredit).
                    </p>

                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded mb-3"
                         style="background:#f8fafc;border:1px solid #e2e8f0">
                        <span class="text-secondary">Total Bayar</span>
                        <span class="h3 mb-0 text-success">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    @if ($order->expires_at)
                        <div id="pay-timer" class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded mb-4"
                             style="background:#fef3c7;border:1px solid #fcd34d;color:#92400e"
                             data-expires-at="{{ $order->expires_at->toIso8601String() }}">
                            <i class="ti ti-clock"></i>
                            <span>Selesaikan pembayaran dalam</span>
                            <span class="fw-bold" id="pay-timer-text">--:--</span>
                        </div>
                    @endif

                    <div class="d-grid gap-2 col-md-8 mx-auto">
                        <button id="pay-button" type="button" class="btn btn-success btn-lg" @if (! $order->snap_token) disabled @endif>
                            <i class="ti ti-external-link me-1"></i> Lanjut Bayar
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

            // Countdown 10 menit — saat habis, refresh ke daftar pesanan agar
            // server menjadwalkan ulang status (Batal) dan menutup popup Snap.
            (function () {
                const wrap = document.getElementById('pay-timer');
                const txt  = document.getElementById('pay-timer-text');
                if (! wrap || ! txt) return;
                const target  = new Date(wrap.dataset.expiresAt).getTime();
                const goneUrl = @json(route('pelanggan.pesanan.index'));

                function pad(n) { return String(n).padStart(2, '0'); }

                function tick() {
                    const diff = target - Date.now();
                    if (diff <= 0) {
                        txt.textContent = '00:00';
                        wrap.style.background = '#fee2e2';
                        wrap.style.borderColor = '#fca5a5';
                        wrap.style.color = '#991b1b';
                        const btn = document.getElementById('pay-button');
                        if (btn) btn.disabled = true;
                        setTimeout(() => window.location.href = goneUrl, 1500);
                        return;
                    }
                    const m = Math.floor(diff / 60000);
                    const s = Math.floor((diff % 60000) / 1000);
                    txt.textContent = pad(m) + ':' + pad(s);
                    requestAnimationFrame(() => setTimeout(tick, 250));
                }
                tick();
            })();
        </script>
    @endpush
</x-layouts.storefront>
