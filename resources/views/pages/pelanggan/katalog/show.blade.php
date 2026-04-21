@php
    $title  = 'Detail Produk';
    $active = 'pelanggan.katalog';
    $produk = \App\Models\Product::with('category', 'petani')->inRandomOrder()->first();
    $terkait = $produk
        ? \App\Models\Product::with('category')
            ->where('id', '!=', $produk->id)
            ->where('category_id', $produk->category_id)
            ->limit(4)
            ->get()
        : collect();
@endphp

<x-layouts.app :title="$title" :active="$active">
    @push('styles')
        <style>
            .product-hero-img { border-radius: .75rem; overflow: hidden; background: #f8f9fa; }
            .product-hero-img img { width: 100%; height: 100%; object-fit: cover; display:block; }
            .thumb { width: 72px; height: 72px; border-radius:.5rem; overflow:hidden; cursor:pointer; border:2px solid transparent; }
            .thumb.active { border-color: var(--tblr-primary); }
            .thumb img { width:100%; height:100%; object-fit:cover; }
            .qty-btn { width: 36px; height: 36px; border: 1px solid #dee2e6; background:#fff; border-radius:.375rem; }
            .mini-card { transition: transform .15s ease; }
            .mini-card:hover { transform: translateY(-2px); }
        </style>
    @endpush

    @if ($produk)
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-arrows">
                <li class="breadcrumb-item"><a href="{{ route('pelanggan.katalog.index') }}">Katalog</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pelanggan.katalog.index', ['category' => $produk->category_id]) }}">{{ $produk->category?->nama }}</a></li>
                <li class="breadcrumb-item active">{{ $produk->nama }}</li>
            </ol>
        </nav>

        <div class="row row-cards">
            <div class="col-md-6">
                <div class="product-hero-img ratio ratio-1x1 mb-3">
                    <img src="{{ $produk->image_url }}" alt="{{ $produk->nama }}">
                </div>
                <div class="d-flex gap-2">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="thumb {{ $i === 0 ? 'active' : '' }}">
                            <img src="{{ $produk->image_url }}" alt="thumb">
                        </div>
                    @endfor
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <span class="badge bg-primary-lt mb-2">{{ $produk->category?->nama }}</span>
                        <h1 class="h2 mb-1">{{ $produk->nama }}</h1>
                        <div class="text-secondary mb-3">
                            Dijual oleh <a href="#" class="text-decoration-none">{{ $produk->petani?->name }}</a>
                            <span class="badge bg-green-lt ms-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5l10 -10"/></svg>
                                Terverifikasi
                            </span>
                        </div>

                        <div class="d-flex align-items-baseline gap-2 mb-3">
                            <div class="h1 text-primary mb-0">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>
                            <div class="text-secondary">/ kg</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="small text-secondary">Stok</div>
                                    <div class="fw-bold">{{ $produk->stok }} kg</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="small text-secondary">Dikirim dari</div>
                                    <div class="fw-bold text-truncate">{{ $produk->petani?->alamat ?? 'Lokal' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="fw-bold mb-1">Deskripsi</div>
                            <p class="text-secondary mb-0">{{ $produk->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                        </div>

                        <form action="#" method="POST">
                            @csrf
                            <label class="form-label">Jumlah</label>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown()">−</button>
                                <input type="number" name="jumlah" class="form-control text-center" style="max-width:80px" value="1" min="1" max="{{ $produk->stok }}">
                                <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp()">+</button>
                                <span class="text-secondary small">Tersedia {{ $produk->stok }}</span>
                            </div>

                            <div class="row g-2">
                                <div class="col">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
                                        Masukkan Keranjang
                                    </button>
                                </div>
                                <div class="col">
                                    <a href="{{ route('pelanggan.checkout.index') }}" class="btn btn-primary w-100">Beli Sekarang</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($terkait->isNotEmpty())
            <div class="mt-4">
                <h3 class="mb-3">Produk Terkait</h3>
                <div class="row row-cards">
                    @foreach ($terkait as $rel)
                        <div class="col-6 col-md-3">
                            <div class="card mini-card h-100">
                                <div class="ratio ratio-1x1 bg-light">
                                    <img src="{{ $rel->image_url }}" alt="{{ $rel->nama }}" class="object-cover" loading="lazy">
                                </div>
                                <div class="card-body p-2">
                                    <div class="small text-truncate">{{ $rel->nama }}</div>
                                    <div class="fw-bold text-primary">Rp {{ number_format($rel->harga, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div class="card"><div class="card-body text-center text-secondary py-5">Produk tidak ditemukan.</div></div>
    @endif
</x-layouts.app>
