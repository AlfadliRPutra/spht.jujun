@php
    use App\Enums\OrderStatus;
    use App\Models\OrderItem;

    $verificationStatus = $user->verificationStatus();
    $showVerifyModal    = ! $user->is_verified && ! session('verifyModalDismissed');

    $produkCount  = $user->products()->count();
    $stokTotal    = $user->products()->sum('stok');
    $lowStok      = $user->products()->where('stok', '<=', 20)->orderBy('stok')->limit(5)->get();
    $pesananBaru  = OrderItem::with(['order.user', 'product'])
        ->whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Dibayar))
        ->latest('id')
        ->get()
        ->groupBy('order_id');
    $totalPendapatan = OrderItem::whereHas('product', fn ($q) => $q->where('user_id', $user->id))
        ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Selesai))
        ->get()
        ->sum(fn ($i) => $i->harga * $i->jumlah);
@endphp

<div class="row row-cards mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="mb-1">Halo, {{ $user->name }}</h2>
                    <div class="text-secondary">Kelola produk, pesanan, dan laporan penjualan Anda di sini.</div>
                </div>
                <a href="{{ route('petani.produk.create') }}" class="btn btn-primary">+ Tambah Produk</a>
            </div>
        </div>
    </div>

    @unless ($user->is_verified)
        <div class="col-12">
            <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                <i class="ti ti-alert-triangle me-2" style="font-size:1.25rem"></i>
                <div class="flex-fill">
                    <div class="fw-semibold">Akun Anda belum terverifikasi</div>
                    <div class="small">
                        @if ($verificationStatus === 'pending')
                            Data verifikasi sudah dikirim dan sedang direview admin. Produk Anda belum tampil di katalog hingga verifikasi disetujui.
                        @elseif ($verificationStatus === 'rejected')
                            Pengajuan sebelumnya ditolak. Silakan perbaiki & ajukan ulang agar produk bisa tampil di katalog.
                        @else
                            Anda bisa menambah produk sekarang, tapi produk belum akan tampil di katalog sampai verifikasi disetujui admin.
                        @endif
                    </div>
                </div>
                <a href="{{ route('petani.verifikasi.index') }}" class="btn btn-warning btn-sm ms-3">
                    {{ $verificationStatus === 'pending' ? 'Lihat Status' : 'Lengkapi Verifikasi' }}
                </a>
            </div>
        </div>
    @endunless

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="text-secondary">Produk Saya</div>
            <div class="h1 mb-0">{{ $produkCount }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="text-secondary">Total Stok</div>
            <div class="h1 mb-0">{{ $stokTotal }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="text-secondary">Pesanan Menunggu</div>
            <div class="h1 mb-0">{{ $pesananBaru->count() }}</div>
        </div></div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm"><div class="card-body">
            <div class="text-secondary">Pendapatan</div>
            <div class="h3 mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="row row-cards">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Pesanan Perlu Diproses</h3>
                <a href="{{ route('petani.pesanan.index') }}" class="btn btn-ghost-primary btn-sm">Semua →</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($pesananBaru->take(5) as $orderId => $items)
                    @php($order = $items->first()->order)
                    <div class="list-group-item d-flex align-items-center">
                        <div class="flex-fill">
                            <div class="fw-semibold">Order #{{ $order->id }} · {{ $order->user->name }}</div>
                            <div class="text-secondary small">{{ $items->pluck('product.nama')->implode(', ') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">Rp {{ number_format($items->sum(fn ($i) => $i->harga * $i->jumlah), 0, ',', '.') }}</div>
                            <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-secondary py-4">Tidak ada pesanan menunggu.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Stok Menipis</h3>
                <a href="{{ route('petani.produk.index') }}" class="btn btn-ghost-primary btn-sm">Kelola →</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($lowStok as $p)
                    <div class="list-group-item d-flex align-items-center gap-2">
                        <img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:44px;height:44px;object-fit:cover">
                        <div class="flex-fill">
                            <div class="fw-semibold">{{ $p->nama }}</div>
                            <div class="text-secondary small">{{ $p->category?->nama }}</div>
                        </div>
                        <span class="badge bg-yellow">{{ $p->stok }} tersisa</span>
                    </div>
                @empty
                    <div class="list-group-item text-center text-secondary py-4">Semua stok aman.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if ($showVerifyModal)
    <div class="modal modal-blur fade" id="verifyReminderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-status bg-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}"></div>
                <div class="modal-body text-center py-4">
                    <i class="ti ti-{{ $verificationStatus === 'pending' ? 'clock' : ($verificationStatus === 'rejected' ? 'circle-x' : 'shield-check') }} mb-2 text-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}" style="font-size:3rem"></i>
                    <h3>
                        @if ($verificationStatus === 'pending')
                            Verifikasi sedang direview
                        @elseif ($verificationStatus === 'rejected')
                            Pengajuan verifikasi ditolak
                        @else
                            Lengkapi verifikasi akun
                        @endif
                    </h3>
                    <div class="text-secondary">
                        @if ($verificationStatus === 'pending')
                            Tim admin sedang memeriksa data Anda. Selama masa review, produk Anda belum tampil di katalog publik.
                        @elseif ($verificationStatus === 'rejected')
                            Silakan lihat catatan admin lalu perbaiki data pengajuan Anda.
                        @else
                            Agar produk Anda tampil di katalog dan bisa dibeli pelanggan, lengkapi data usaha & KTP terlebih dahulu. Verifikasi akan direview admin.
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="{{ route('petani.verifikasi.dismiss') }}" class="w-auto me-auto">
                        @csrf
                        <button type="submit" class="btn btn-link link-secondary">Nanti saja</button>
                    </form>
                    <a href="{{ route('petani.verifikasi.index') }}" class="btn btn-{{ $verificationStatus === 'pending' ? 'info' : ($verificationStatus === 'rejected' ? 'danger' : 'warning') }}">
                        {{ $verificationStatus === 'pending' ? 'Lihat Status' : ($verificationStatus === 'rejected' ? 'Perbaiki Pengajuan' : 'Lengkapi Sekarang') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('verifyReminderModal');
                if (el && window.bootstrap) {
                    new bootstrap.Modal(el).show();
                }
            });
        </script>
    @endpush
@endif
