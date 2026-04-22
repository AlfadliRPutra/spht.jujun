@php
    /** @var \App\Models\Product $produk */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $terkait */
    $title  = $produk->nama;
    $active = 'pelanggan.katalog';
    $parentCategory = $produk->category?->parent;
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            .product-hero-img { border-radius: .75rem; overflow: hidden; background: #f6f8fa; aspect-ratio: 1/1; }
            .product-hero-img img { width: 100%; height: 100%; object-fit: contain; padding: 1.5rem; display: block; }
            .thumb { width: 72px; height: 72px; border-radius:.5rem; overflow:hidden; cursor:pointer; border:2px solid transparent; background:#f6f8fa; }
            .thumb.active { border-color: #0b5d2b; }
            .thumb img { width:100%; height:100%; object-fit:contain; padding:.35rem; }
            .qty-btn { width: 36px; height: 36px; border: 1px solid #dee2e6; background:#fff; border-radius:.375rem; font-size:1.1rem; line-height:1; }
            .mini-card { transition: transform .15s ease; border: 1px solid #eef0f3; border-radius: .75rem; overflow: hidden; background: #fff; }
            .mini-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.06); }
            .mini-card .ratio { background: #f6f8fa; }
            .mini-card img { object-fit: contain !important; padding: .5rem; }
            .price-tag { color: #0b5d2b; }
        </style>
    @endpush

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb breadcrumb-arrows">
            <li class="breadcrumb-item"><a href="{{ route('pelanggan.katalog.index') }}">Katalog</a></li>
            @if ($parentCategory)
                <li class="breadcrumb-item">
                    <a href="{{ route('pelanggan.katalog.index', ['category' => $parentCategory->slug]) }}">{{ $parentCategory->nama }}</a>
                </li>
            @endif
            @if ($produk->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('pelanggan.katalog.index', ['category' => $produk->category->slug]) }}">{{ $produk->category->nama }}</a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ $produk->nama }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="product-hero-img mb-3">
                <img src="{{ $produk->image_url }}" alt="{{ $produk->nama }}" fetchpriority="high" decoding="async">
            </div>
            <div class="d-flex gap-2">
                @for ($i = 0; $i < 4; $i++)
                    <div class="thumb {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ $produk->image_url }}" alt="thumb" loading="lazy" decoding="async">
                    </div>
                @endfor
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <span class="badge bg-green-lt mb-2">{{ $produk->category?->nama }}</span>
                    <h1 class="h2 mb-1">{{ $produk->nama }}</h1>
                    <div class="text-secondary mb-3">
                        Dijual oleh <strong>{{ $produk->petani?->name }}</strong>
                        <span class="badge bg-green-lt ms-1">
                            <i class="ti ti-check me-1"></i> Terverifikasi
                        </span>
                    </div>

                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <div class="h1 price-tag mb-0">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>
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
                                <div class="small text-secondary">Terjual</div>
                                <div class="fw-bold">{{ $produk->sold_count }} kg</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="fw-bold mb-1">Deskripsi</div>
                        <p class="text-secondary mb-0">{{ $produk->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                    </div>

                    @auth
                        <form action="{{ route('pelanggan.keranjang.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $produk->id }}">
                            <label class="form-label">Jumlah</label>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown()">−</button>
                                <input type="number" name="jumlah" class="form-control text-center" style="max-width:80px" value="1" min="1" max="{{ $produk->stok }}">
                                <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp()">+</button>
                                <span class="text-secondary small">Tersedia {{ $produk->stok }}</span>
                            </div>

                            <div class="row g-2">
                                <div class="col">
                                    <button type="submit" class="btn btn-outline-success w-100">
                                        <i class="ti ti-shopping-cart-plus me-2"></i> Masukkan Keranjang
                                    </button>
                                </div>
                                <div class="col">
                                    <a href="{{ route('pelanggan.checkout.index') }}" class="btn btn-success w-100">Beli Sekarang</a>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info mb-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>Masuk dulu untuk memasukkan produk ke keranjang.</div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('login') }}" class="btn btn-success">Masuk</a>
                                    <a href="#" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#registerRoleModal">Daftar</a>
                                </div>
                            </div>
                        </div>
                    @endauth
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
                        <a href="{{ route('pelanggan.katalog.show', $rel->slug) }}" class="text-reset text-decoration-none">
                            <div class="mini-card h-100 reveal" style="animation-delay: {{ $loop->index * 60 }}ms">
                                <div class="ratio ratio-1x1">
                                    <img src="{{ $rel->image_url }}" alt="{{ $rel->nama }}" loading="lazy" decoding="async">
                                </div>
                                <div class="p-2">
                                    <div class="small text-truncate">{{ $rel->nama }}</div>
                                    <div class="fw-bold price-tag">Rp {{ number_format($rel->harga, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.storefront>
