@php
    use App\Enums\PaymentMethod;

    $title  = 'Checkout';
    $active = 'pelanggan.keranjang';
    $user   = auth()->user();
    $stores            = $checkout['stores'] ?? [];
    $subtotalProduk    = $checkout['subtotal_produk'] ?? 0;
    $shippingTotal     = $checkout['shipping_total'] ?? 0;
    $grandTotal        = $checkout['grand_total'] ?? 0;
    $hasBlockedStore   = $checkout['has_blocked_store'] ?? false;
    $checkoutErrors    = $checkout['errors'] ?? [];
    /** @var \App\Enums\PaymentMethod $paymentMethod */
    $paymentMethod   ??= PaymentMethod::Online;
    /** @var array<int,\App\Enums\PaymentMethod> $paymentMethods */
    $paymentMethods  ??= PaymentMethod::cases();
    $isPickup          = $paymentMethod->isPickup();
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @if (empty($stores))
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                     style="width:72px;height:72px;background:#fef3c7;color:#b45309">
                    <i class="ti ti-shopping-cart-off" style="font-size:2rem"></i>
                </div>
                <h3>Keranjang kosong</h3>
                <p class="text-secondary mb-3">Tambahkan produk sebelum melakukan checkout.</p>
                <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        </div>
    @else
        @if (! empty($checkoutErrors))
            <div class="alert alert-warning">
                <ul class="mb-0">
                    @foreach ($checkoutErrors as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pelanggan.pembayaran.store') }}" method="POST" class="row row-cards">
            @csrf
            <input type="hidden" name="address_id"     value="{{ $selectedAddress->id }}">
            <input type="hidden" name="payment_method" value="{{ $paymentMethod->value }}">

            <div class="col-md-7">
                {{-- Pemilih metode pengiriman & pembayaran --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Metode Pengiriman &amp; Pembayaran</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach ($paymentMethods as $pm)
                                @php $isActive = $pm === $paymentMethod; @endphp
                                <div class="col-md-4">
                                    <a href="{{ route('pelanggan.checkout.index', ['address_id' => $selectedAddress->id, 'payment_method' => $pm->value]) }}"
                                       class="d-block p-3 rounded border h-100 text-decoration-none {{ $isActive ? 'border-success bg-success-lt' : 'text-body' }}"
                                       style="transition:all .15s">
                                        <div class="d-flex align-items-start gap-2 mb-1">
                                            <div class="avatar avatar-sm bg-light rounded">
                                                <i class="ti ti-{{ $pm->icon() }}" style="font-size:1.25rem"></i>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="fw-semibold">{{ $pm->label() }}</div>
                                                <div class="small text-secondary">{{ $pm->description() }}</div>
                                            </div>
                                            @if ($isActive)
                                                <i class="ti ti-circle-check text-success"></i>
                                            @else
                                                <i class="ti ti-circle text-secondary"></i>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">
                            {{ $isPickup ? 'Data Pengambil' : 'Alamat Pengiriman' }}
                        </h3>
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-plus me-1"></i> Kelola Alamat
                        </a>
                    </div>
                    <div class="card-body">
                        @if ($addresses->count() > 1)
                            <div class="mb-3">
                                <label class="form-label small text-secondary mb-2">Pilih dari alamat tersimpan ({{ $addresses->count() }}):</label>
                                <div class="row g-2">
                                    @foreach ($addresses as $addr)
                                        @php $isSelected = $addr->id === $selectedAddress->id; @endphp
                                        <div class="col-md-6">
                                            <a href="{{ route('pelanggan.checkout.index', ['address_id' => $addr->id, 'payment_method' => $paymentMethod->value]) }}"
                                               class="d-block p-3 rounded border text-decoration-none {{ $isSelected ? 'border-success bg-success-lt' : 'text-body' }}"
                                               style="transition:all .15s">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div>
                                                        <span class="fw-bold">{{ $addr->label ?: 'Alamat' }}</span>
                                                        @if ($addr->is_default)
                                                            <span class="badge bg-success-lt text-success border-0 ms-1"><i class="ti ti-star-filled me-1"></i>Utama</span>
                                                        @endif
                                                    </div>
                                                    @if ($isSelected)
                                                        <i class="ti ti-circle-check text-success"></i>
                                                    @else
                                                        <i class="ti ti-circle text-secondary"></i>
                                                    @endif
                                                </div>
                                                <div class="small">{{ $addr->nama_penerima }} · {{ $addr->no_hp_penerima }}</div>
                                                <div class="small text-secondary">
                                                    {{ $addr->district_name }}, {{ $addr->city_name }}
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr>
                        @endif

                        <div class="row g-3 small">
                            <div class="col-md-6">
                                <div class="text-secondary">{{ $isPickup ? 'Yang Mengambil' : 'Penerima' }}</div>
                                <div class="fw-semibold">{{ $selectedAddress->nama_penerima }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary">No. HP</div>
                                <div class="fw-semibold">{{ $selectedAddress->no_hp_penerima }}</div>
                            </div>
                            @unless ($isPickup)
                                <div class="col-12">
                                    <div class="text-secondary">Wilayah</div>
                                    <div>{{ $selectedAddress->district_name }}, {{ $selectedAddress->city_name }}, {{ $selectedAddress->province_name }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-secondary">Alamat Lengkap</div>
                                    <div>{{ $selectedAddress->alamat }}</div>
                                </div>
                            @else
                                <div class="col-12">
                                    <div class="alert alert-info py-2 small mb-0">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Data ini hanya untuk identifikasi pengambil. Anda akan datang langsung ke alamat masing-masing toko di bawah.
                                    </div>
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>

                {{-- Daftar item dikelompokkan per toko --}}
                @foreach ($stores as $group)
                    @php
                        $store    = $group['store'];
                        $shipping = $group['shipping'];
                        $available = $shipping['available'];
                    @endphp
                    <div class="card mb-3 @if (! $available) border-danger @endif">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title mb-0">
                                    <i class="ti ti-building-store me-1"></i>
                                    {{ $store->nama_usaha ?: $store->name }}
                                </h3>
                                <div class="text-secondary small">
                                    @if ($store->hasCompleteAddress())
                                        {{ $store->district_name }}, {{ $store->city_name }}
                                    @else
                                        <span class="text-danger">Alamat toko belum lengkap</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge {{ $available ? 'bg-green-lt' : 'bg-red-lt' }}">
                                {{ $shipping['zone_label'] }}
                            </span>
                        </div>
                        <div class="card-body">
                            @foreach ($group['items'] as $item)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product->nama }}"
                                         class="rounded" style="width:44px;height:44px;object-fit:cover" loading="lazy" decoding="async">
                                    <div class="flex-fill">
                                        <div class="small fw-semibold text-truncate">{{ $item->product->nama }}</div>
                                        <div class="text-secondary small">
                                            × {{ $item->jumlah }}
                                            · {{ rtrim(rtrim(number_format($item->product->weight_kg, 3, ',', '.'), '0'), ',') }} kg/unit
                                        </div>
                                    </div>
                                    <div class="small">Rp {{ number_format($item->product->harga * $item->jumlah, 0, ',', '.') }}</div>
                                </div>
                            @endforeach

                            <hr class="my-2">

                            <div class="row text-center small">
                                <div class="col-4">
                                    <div class="text-secondary">Total Berat</div>
                                    <div class="fw-semibold">{{ $group['total_weight_kg'] }} kg</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-secondary">Subtotal Produk</div>
                                    <div class="fw-semibold">Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-secondary">Ongkir</div>
                                    <div class="fw-semibold {{ $available ? '' : 'text-danger' }}">
                                        @if ($available)
                                            Rp {{ number_format($shipping['shipping_cost'], 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert {{ $available ? 'alert-info' : 'alert-danger' }} mt-3 mb-0 py-2 small">
                                <i class="ti {{ $available ? 'ti-info-circle' : 'ti-alert-triangle' }} me-1"></i>
                                {{ $shipping['message'] }}
                                @if ($available)
                                    <div class="text-secondary mt-1">
                                        Tarif dasar Rp {{ number_format($shipping['base_fee'], 0, ',', '.') }}
                                        ({{ $shipping['base_weight_kg'] }} kg pertama)
                                        + Rp {{ number_format($shipping['extra_fee_per_kg'], 0, ',', '.') }}/kg untuk kelebihan.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="card">
                    <div class="card-header"><h3 class="card-title">Yang Akan Terjadi Setelah Klik Buat Pesanan</h3></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 p-3 rounded border" style="background:#f8fafc">
                            <div class="avatar avatar-md bg-primary-lt rounded">
                                <i class="ti ti-{{ $paymentMethod->icon() }}" style="font-size:1.6rem"></i>
                            </div>
                            <div class="flex-fill">
                                <div class="fw-semibold">{{ $paymentMethod->label() }}</div>
                                <div class="text-secondary small">
                                    @switch($paymentMethod)
                                        @case(PaymentMethod::Online)
                                            Anda akan diarahkan ke halaman Midtrans untuk memilih VA/QRIS/e-wallet/kartu.
                                            Stok akan dikurangi setelah pembayaran berhasil.
                                            @break
                                        @case(PaymentMethod::Cod)
                                            Pesanan langsung diteruskan ke petani untuk dikemas. Bayar tunai ke kurir/petani saat barang sampai.
                                            Stok dikurangi sekarang juga.
                                            @break
                                        @case(PaymentMethod::Pickup)
                                            Pesanan langsung diteruskan ke petani. Datang ke alamat toko di atas, bayar tunai saat ambil.
                                            Stok dikurangi sekarang juga.
                                            @break
                                    @endswitch
                                </div>
                            </div>
                            <span class="badge {{ $paymentMethod->usesMidtrans() ? 'bg-green-lt' : 'bg-yellow-lt' }}">
                                <i class="ti ti-{{ $paymentMethod->usesMidtrans() ? 'shield-check' : 'cash' }} me-1"></i>
                                {{ $paymentMethod->usesMidtrans() ? 'Aman (Midtrans)' : 'Tunai' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Ringkasan Pesanan</h3></div>
                    <div class="card-body">
                        @foreach ($stores as $group)
                            <div class="mb-2">
                                <div class="small fw-semibold">
                                    {{ $group['store']->nama_usaha ?: $group['store']->name }}
                                </div>
                                <div class="d-flex justify-content-between small text-secondary">
                                    <span>Subtotal · {{ $group['total_weight_kg'] }} kg</span>
                                    <span>Rp {{ number_format($group['subtotal'], 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-secondary">
                                    <span>Ongkir ({{ $group['shipping']['zone_label'] }})</span>
                                    <span>
                                        @if ($group['shipping']['available'])
                                            Rp {{ number_format($group['shipping']['shipping_cost'], 0, ',', '.') }}
                                        @else
                                            <span class="text-danger">-</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Subtotal Produk</span>
                            <span>Rp {{ number_format($subtotalProduk, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Total Ongkir</span>
                            <span>Rp {{ number_format($shippingTotal, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h4 mb-0">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success w-100" @disabled($hasBlockedStore)>
                            <i class="ti ti-{{ $paymentMethod->usesMidtrans() ? 'credit-card' : 'check' }} me-1"></i>
                            {{ $paymentMethod->usesMidtrans() ? 'Lanjut Pembayaran' : 'Buat Pesanan' }}
                        </button>
                        @if ($hasBlockedStore)
                            <div class="text-danger small text-center mt-2">
                                Hapus produk dari toko yang tidak dapat dikirim sebelum melanjutkan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    @endif
</x-layouts.storefront>
