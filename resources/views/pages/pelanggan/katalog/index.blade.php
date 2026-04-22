@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $produk */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $rootCategories */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $subCategories */
    /** @var \App\Models\Category|null $selectedCategory */
    /** @var string|null $activeRootSlug */
    /** @var string $sort */
    /** @var array $sortOptions */
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\HeroSlide> $heroSlides */
    $title  = 'Katalog Produk';
    $active = 'pelanggan.katalog';

    $queryBase = array_filter([
        'q'        => request('q'),
        'category' => request('category'),
        'sort'     => request('sort'),
    ], fn ($v) => $v !== null && $v !== '');
@endphp

<x-layouts.storefront :title="$title" :active="$active">
    @push('styles')
        <style>
            .hero-slide { position: relative; height: 360px; border-radius: .75rem; overflow: hidden; }
            .hero-slide img { width: 100%; height: 100%; object-fit: cover; }
            .hero-slide::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,.55) 0%, rgba(0,0,0,.1) 70%); }
            .hero-caption { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: center; padding: 2rem 2.5rem; color: #fff; z-index: 2; max-width: 60%; }
            .hero-caption h2 { font-weight: 700; font-size: 2rem; margin-bottom: .5rem; }
            @media (max-width: 576px) {
                .hero-slide { height: 260px; }
                .hero-caption { padding: 1.25rem; max-width: 100%; }
                .hero-caption h2 { font-size: 1.4rem; }
            }

            .filter-pills { display: flex; gap: .5rem; overflow-x: auto; padding-bottom: .5rem; }
            .filter-pills::-webkit-scrollbar { height: 4px; }
            .filter-pills .pill { white-space: nowrap; border-radius: 999px; padding: .35rem .9rem; font-size: .875rem; border: 1px solid #dee2e6; background: #fff; color: #24344d; text-decoration: none; }
            .filter-pills .pill.active { background: #0b5d2b; color: #fff; border-color: #0b5d2b; }
            .filter-pills .pill:not(.active):hover { background: #f0fdf4; border-color: #0b5d2b; color: #0b5d2b; }

            .product-card { border: 1px solid #eef0f3; border-radius: .75rem; overflow: hidden; background: #fff; transition: transform .15s ease, box-shadow .15s ease; height: 100%; display: flex; flex-direction: column; }
            .product-card:hover { transform: translateY(-3px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.06); }
            .product-media { aspect-ratio: 1/1; background: #f6f8fa; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
            .product-media img { width: 100%; height: 100%; object-fit: contain; padding: .75rem; transition: transform .3s ease; }
            .product-card:hover .product-media img { transform: scale(1.04); }
            .product-badge { position: absolute; top: .5rem; left: .5rem; background: #0b5d2b; color: #fff; font-size: .7rem; padding: .15rem .5rem; border-radius: 999px; z-index: 2; }
            .product-badge.danger { background: #d63939; left: auto; right: .5rem; }
            .product-body { padding: .85rem 1rem .5rem; flex: 1 1 auto; }
            .product-name { font-weight: 600; color: #1f2d3d; margin-bottom: .15rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; }
            .product-seller { font-size: .75rem; color: #6b7a90; margin-bottom: .35rem; }
            .product-price { color: #0b5d2b; font-weight: 700; font-size: 1.1rem; }
            .product-meta { font-size: .75rem; color: #6b7a90; margin-top: .15rem; }
            .product-footer { padding: 0 1rem 1rem; display: flex; gap: .5rem; }
            .product-footer .btn { flex: 1; }
        </style>
    @endpush

    @if ($heroSlides->isNotEmpty())
        <div id="heroCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            @if ($heroSlides->count() > 1)
                <div class="carousel-indicators">
                    @foreach ($heroSlides as $i => $slide)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
                    @endforeach
                </div>
            @endif
            <div class="carousel-inner">
                @foreach ($heroSlides as $i => $slide)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <div class="hero-slide">
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}">
                            <div class="hero-caption">
                                <h2>{{ $slide->title }}</h2>
                                @if ($slide->subtitle)
                                    <p class="mb-3 opacity-75">{{ $slide->subtitle }}</p>
                                @endif
                                @if ($slide->cta_label && $slide->cta_url)
                                    <div><a href="{{ $slide->cta_url }}" class="btn btn-success">{{ $slide->cta_label }}</a></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($heroSlides->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            @endif
        </div>
    @endif

    <div class="filter-pills mb-2">
        <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => null])) }}"
           class="pill {{ ! $selectedCategory ? 'active' : '' }}">Semua</a>
        @foreach ($rootCategories as $root)
            <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => $root->slug])) }}"
               class="pill {{ $activeRootSlug === $root->slug ? 'active' : '' }}">
                @if ($root->icon)<i class="ti ti-{{ $root->icon }} me-1"></i>@endif
                {{ $root->nama }}
            </a>
        @endforeach
    </div>

    @if ($subCategories->isNotEmpty())
        <div class="filter-pills mb-3">
            @foreach ($subCategories as $sub)
                <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => $sub->slug])) }}"
                   class="pill {{ $selectedCategory?->id === $sub->id ? 'active' : '' }}">
                    {{ $sub->nama }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="text-secondary">
            @if ($selectedCategory)
                Kategori <strong>{{ $selectedCategory->nama }}</strong> &middot;
            @endif
            Menampilkan <strong>{{ $produk->count() }}</strong> produk
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            @foreach ($queryBase as $k => $v)
                @if ($k !== 'sort')
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <label class="text-secondary small mb-0">Urutkan:</label>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:180px">
                @foreach ($sortOptions as $key => [$_, $__, $label])
                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        @forelse ($produk as $item)
            @php($lowStock = $item->stok <= 20)
            <div class="col">
                <div class="product-card">
                    <a href="{{ route('pelanggan.katalog.show', $item->slug) }}" class="product-media text-decoration-none">
                        <span class="product-badge">{{ $item->category?->nama }}</span>
                        @if ($lowStock)
                            <span class="product-badge danger">Stok Terbatas</span>
                        @endif
                        <img src="{{ $item->image_url }}" alt="{{ $item->nama }}" loading="lazy">
                    </a>
                    <div class="product-body">
                        <a href="{{ route('pelanggan.katalog.show', $item->slug) }}" class="product-name d-block text-decoration-none text-reset" title="{{ $item->nama }}">
                            {{ $item->nama }}
                        </a>
                        <div class="product-seller"><i class="ti ti-user me-1"></i>{{ $item->petani?->name }}</div>
                        <div class="product-price">Rp {{ number_format($item->harga, 0, ',', '.') }}</div>
                        <div class="product-meta">Stok {{ $item->stok }} &middot; Terjual {{ $item->sold_count }}</div>
                    </div>
                    <div class="product-footer">
                        <a href="{{ route('pelanggan.katalog.show', $item->slug) }}" class="btn btn-outline-success btn-sm">Detail</a>
                        @auth
                            <form method="POST" action="{{ route('pelanggan.keranjang.store') }}" class="flex-fill m-0">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <input type="hidden" name="jumlah" value="1">
                                <button type="submit" class="btn btn-success btn-sm w-100" title="Masukkan keranjang">
                                    <i class="ti ti-shopping-cart-plus"></i>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-success btn-sm" title="Masuk untuk belanja">
                                <i class="ti ti-shopping-cart-plus"></i>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty">
                    <p class="empty-title">Produk tidak ditemukan</p>
                    <p class="empty-subtitle text-secondary">Coba ubah kata kunci atau pilih kategori lain.</p>
                    <div class="empty-action">
                        <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-success">Reset Pencarian</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</x-layouts.storefront>
