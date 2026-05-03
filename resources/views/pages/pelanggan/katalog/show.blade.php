@php
    /** @var \App\Models\Product $produk */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $terkait */
    $title  = $produk->nama;
    $active = 'pelanggan.katalog';
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            .product-hero-img { border-radius: .75rem; overflow: hidden; background: #f6f8fa; aspect-ratio: 1/1; }
            .product-hero-img img { width: 100%; height: 100%; object-fit: contain; padding: 1.5rem; display: block; }
            .thumb { width: 72px; height: 72px; border-radius:.5rem; overflow:hidden; cursor:pointer; border:2px solid transparent; background:#f6f8fa; padding:0; }
            .thumb.active { border-color: #0b5d2b; }
            .thumb:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(11,93,43,.25); }
            .thumb img { width:100%; height:100%; object-fit:contain; padding:.35rem; display:block; }
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
            @if ($produk->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('pelanggan.katalog.index', ['category' => $produk->category->slug]) }}">{{ $produk->category->nama }}</a>
                </li>
            @endif
            @if ($produk->subCategory)
                <li class="breadcrumb-item">
                    <a href="{{ route('pelanggan.katalog.index', ['category' => $produk->category->slug, 'sub' => $produk->subCategory->slug]) }}">{{ $produk->subCategory->nama }}</a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ $produk->nama }}</li>
        </ol>
    </nav>

    @php
        /** @var string[] $galleryUrls */
        $galleryUrls = $produk->gallery_urls;
    @endphp
    <div class="row g-4">
        <div class="col-md-6">
            <div class="product-hero-img mb-3">
                <img id="produk-hero-img" src="{{ $galleryUrls[0] }}" alt="{{ $produk->nama }}" fetchpriority="high" decoding="async">
            </div>
            @if (count($galleryUrls) > 1)
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($galleryUrls as $idx => $url)
                        <button type="button"
                                class="thumb js-thumb {{ $idx === 0 ? 'active' : '' }}"
                                data-src="{{ $url }}"
                                aria-label="Tampilkan foto {{ $idx + 1 }}">
                            <img src="{{ $url }}" alt="Foto {{ $idx + 1 }}" loading="lazy" decoding="async">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="mb-2">
                        @if ($produk->category)
                            <span class="badge bg-blue-lt">{{ $produk->category->nama }}</span>
                        @endif
                        @if ($produk->subCategory)
                            <i class="ti ti-chevron-right text-secondary mx-1"></i>
                            <span class="badge bg-green-lt">{{ $produk->subCategory->nama }}</span>
                        @endif
                    </div>
                    <h1 class="h2 mb-1">{{ $produk->nama }}</h1>
                    <div class="text-secondary mb-3">
                        Dijual oleh <strong>{{ $produk->petani?->name }}</strong>
                        <span class="badge bg-green-lt ms-1">
                            <i class="ti ti-check me-1"></i> Terverifikasi
                        </span>
                    </div>

                    @php
                        $weightStr = rtrim(rtrim(number_format((float) $produk->weight_kg, 3, ',', '.'), '0'), ',');
                    @endphp
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <div class="h1 price-tag mb-0">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>
                        <div class="text-secondary">/ unit</div>
                    </div>
                    <div class="text-secondary small mb-3">
                        <i class="ti ti-weight me-1"></i>Berat {{ $weightStr }} kg / unit
                        <span class="text-muted">(dipakai untuk perhitungan ongkir)</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="small text-secondary">Stok</div>
                                <div class="fw-bold">{{ $produk->stok }} unit</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="small text-secondary">Terjual</div>
                                <div class="fw-bold">{{ $produk->sold_count }} unit</div>
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
                            <label class="form-label">Jumlah (unit)</label>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown()">−</button>
                                <input type="number" name="jumlah" class="form-control text-center" style="max-width:80px" value="1" min="1" max="{{ $produk->stok }}">
                                <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp()">+</button>
                                <span class="text-secondary small">Tersedia {{ $produk->stok }} unit</span>
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

    @push('scripts')
        <script>
            (function () {
                const hero = document.getElementById('produk-hero-img');
                const thumbs = document.querySelectorAll('.js-thumb');
                if (!hero || thumbs.length === 0) return;

                thumbs.forEach(btn => {
                    btn.addEventListener('click', () => {
                        const src = btn.dataset.src;
                        if (!src) return;
                        hero.src = src;
                        thumbs.forEach(t => t.classList.toggle('active', t === btn));
                    });
                });
            })();
        </script>
    @endpush

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
