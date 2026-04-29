@php
    $title  = 'Checkout';
    $active = 'pelanggan.keranjang';
    $user   = auth()->user();
    $stores            = $checkout['stores'] ?? [];
    $subtotalProduk    = $checkout['subtotal_produk'] ?? 0;
    $shippingTotal     = $checkout['shipping_total'] ?? 0;
    $voucherDiscount   = $checkout['voucher_discount'] ?? 0;
    $grandTotal        = $checkout['grand_total'] ?? 0;
    $hasBlockedStore   = $checkout['has_blocked_store'] ?? false;
    $checkoutErrors    = $checkout['errors'] ?? [];
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
                @if (! $user->hasCompleteAddress())
                    <div class="mt-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-warning">
                            <i class="ti ti-map-pin me-1"></i> Lengkapi Alamat
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <form action="{{ route('pelanggan.pembayaran.store') }}" method="POST" class="row row-cards">
            @csrf
            <div class="col-md-7">
                <div class="card mb-3">
                    <div class="card-header"><h3 class="card-title">Alamat Pengiriman</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Penerima</label>
                            <input type="text" name="nama_penerima" class="form-control @error('nama_penerima') is-invalid @enderror" value="{{ old('nama_penerima', $user->name) }}" required>
                            @error('nama_penerima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">No. HP</label>
                            <input type="text" name="no_hp_penerima" class="form-control @error('no_hp_penerima') is-invalid @enderror" value="{{ old('no_hp_penerima', $user->no_hp) }}" required>
                            @error('no_hp_penerima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wilayah</label>
                            <div class="form-control bg-light" style="min-height:auto">
                                @if ($user->hasCompleteAddress())
                                    {{ $user->district_name }}, {{ $user->city_name }}, {{ $user->province_name }}
                                @else
                                    <span class="text-danger">Belum lengkap — </span>
                                    <a href="{{ route('profile.edit') }}">atur di profil</a>
                                @endif
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label required">Alamat Lengkap</label>
                            <textarea name="alamat_pengiriman" class="form-control @error('alamat_pengiriman') is-invalid @enderror" rows="3" required>{{ old('alamat_pengiriman', $user->alamat) }}</textarea>
                            @error('alamat_pengiriman')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    <div class="card-header"><h3 class="card-title">Metode Pembayaran</h3></div>
                    <div class="card-body">
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
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Diskon Voucher</span>
                            <span>- Rp {{ number_format($voucherDiscount, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h4 mb-0">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success w-100" @disabled($hasBlockedStore || ! $user->hasCompleteAddress())>
                            <i class="ti ti-credit-card me-1"></i> Bayar via Midtrans
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
