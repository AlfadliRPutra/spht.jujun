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
            .hero-slide { position: relative; height: 360px; border-radius: var(--spht-radius-lg); overflow: hidden; }
            .hero-slide img { width: 100%; height: 100%; object-fit: cover; }
            /* Dual-layer overlay: horizontal gradient untuk caption area + vertical untuk
               kontrol carousel di bawah. Cukup gelap di sisi kiri agar judul putih
               tetap terbaca pada gambar berwarna terang. */
            .hero-slide::after {
                content: '';
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(90deg, rgba(0,0,0,.78) 0%, rgba(0,0,0,.5) 45%, rgba(0,0,0,.15) 80%),
                    linear-gradient(180deg, rgba(0,0,0,0) 60%, rgba(0,0,0,.4) 100%);
                z-index: 1;
            }
            .hero-caption {
                position: absolute; inset: 0; display: flex; flex-direction: column;
                justify-content: center; padding: 2rem 2.5rem; color: #fff; z-index: 2;
                max-width: 60%;
                text-shadow: 0 2px 12px rgba(0,0,0,.55), 0 1px 3px rgba(0,0,0,.4);
            }
            .hero-caption h2 {
                font-weight: 800; font-size: 2rem; margin-bottom: .55rem; letter-spacing: -.01em;
                line-height: 1.15; color: #fff;
            }
            .hero-caption p { font-size: 1rem; opacity: .95; line-height: 1.45; }
            .hero-caption .btn { text-shadow: none; box-shadow: 0 6px 14px -4px rgba(0,0,0,.4); }
            @media (max-width: 576px) {
                .hero-slide { height: 260px; }
                .hero-slide::after { background: linear-gradient(180deg, rgba(0,0,0,.3) 0%, rgba(0,0,0,.75) 100%); }
                .hero-caption { padding: 1.25rem; max-width: 100%; justify-content: flex-end; padding-bottom: 1.5rem; }
                .hero-caption h2 { font-size: 1.4rem; }
                .hero-caption p { font-size: .9rem; }
            }

            .filter-panel { background: #fff; border: 1px solid var(--spht-border); border-radius: var(--spht-radius); padding: 1rem 1.25rem; }
            .filter-group + .filter-group { margin-top: .85rem; }
            .filter-group-label { font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; color: var(--spht-muted); font-weight: 600; margin-bottom: .5rem; }

            .filter-pills { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .25rem; scrollbar-width: thin; }
            .filter-pills::-webkit-scrollbar { height: 4px; }
            .filter-pills .pill { white-space: nowrap; border-radius: 999px; padding: .38rem .9rem; font-size: .85rem; border: 1px solid var(--spht-border); background: #fff; color: var(--spht-ink); text-decoration: none; transition: background-color .15s ease, color .15s ease, border-color .15s ease; display: inline-flex; align-items: center; gap: .3rem; }
            .filter-pills .pill.active { background: var(--spht-green); color: #fff; border-color: var(--spht-green); }
            .filter-pills .pill:not(.active):hover { background: var(--spht-green-soft); border-color: var(--spht-green); color: var(--spht-green); }
            .filter-pills.sub .pill { font-size: .8rem; padding: .3rem .75rem; }

            .toolbar-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; margin: 1.25rem 0 1rem; }

            .product-card { border: 1px solid var(--spht-border); border-radius: var(--spht-radius); overflow: hidden; background: #fff; transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; height: 100%; display: flex; flex-direction: column; }
            .product-card:hover { transform: translateY(-2px); box-shadow: 0 .6rem 1.25rem rgba(17,24,39,.06); border-color: #d5dbe3; }
            .product-media { position: relative; height: 220px; background: #f7f8fa; overflow: hidden; }
            .product-media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s ease; }
            .product-card:hover .product-media img { transform: scale(1.04); }
            .product-badge { position: absolute; top: .6rem; left: .6rem; background: #fff; color: var(--spht-ink); font-size: .7rem; padding: .2rem .55rem; border-radius: 999px; z-index: 2; border: 1px solid var(--spht-border); font-weight: 500; }
            .product-badge.danger { background: #fff7ed; color: #c2410c; border-color: #fed7aa; left: auto; right: .6rem; }
            .product-body { padding: .85rem 1rem .4rem; flex: 1 1 auto; }
            .product-name { font-weight: 600; color: var(--spht-ink); font-size: .95rem; margin-bottom: .2rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em; line-height: 1.3; }
            .product-seller { font-size: .75rem; color: var(--spht-muted); margin-bottom: .35rem; }
            .product-price { color: var(--spht-green-dark); font-weight: 700; font-size: 1.05rem; }
            .product-meta { font-size: .72rem; color: var(--spht-muted); margin-top: .15rem; }
            .product-footer { padding: 0 1rem 1rem; display: flex; gap: .5rem; }
            .product-footer .btn { flex: 1; }

            @media (max-width: 576px) {
                .product-media { height: 160px; }
            }
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
                            <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}"
                                 @if ($i === 0) fetchpriority="high" decoding="async"
                                 @else loading="lazy" decoding="async" @endif>
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

    <div class="filter-panel">
        <div class="filter-group">
            <div class="filter-group-label">Kategori</div>
            <div class="filter-pills">
                <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => null])) }}"
                   class="pill {{ ! $selectedCategory ? 'active' : '' }}">Semua</a>
                @foreach ($rootCategories as $root)
                    <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => $root->slug])) }}"
                       class="pill {{ $activeRootSlug === $root->slug ? 'active' : '' }}">
                        @if ($root->icon)<i class="ti ti-{{ $root->icon }}"></i>@endif
                        {{ $root->nama }}
                    </a>
                @endforeach
            </div>
        </div>

        @if ($subCategories->isNotEmpty())
            <div class="filter-group">
                <div class="filter-group-label">Sub Kategori</div>
                <div class="filter-pills sub">
                    @foreach ($subCategories as $sub)
                        <a href="{{ route('pelanggan.katalog.index', array_merge($queryBase, ['category' => $sub->slug])) }}"
                           class="pill {{ $selectedCategory?->id === $sub->id ? 'active' : '' }}">
                            {{ $sub->nama }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="toolbar-row">
        <div class="text-secondary small">
            @if ($selectedCategory)
                Kategori <strong class="text-dark">{{ $selectedCategory->nama }}</strong> &middot;
            @endif
            Menampilkan <strong class="text-dark">{{ $produk->count() }}</strong> produk
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            @foreach ($queryBase as $k => $v)
                @if ($k !== 'sort')
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <label class="text-secondary small mb-0">Urutkan</label>
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
                <div class="product-card reveal" style="animation-delay: {{ min($loop->index * 40, 400) }}ms">
                    <a href="{{ route('pelanggan.katalog.show', $item->slug) }}" class="product-media text-decoration-none">
                        <span class="product-badge">{{ $item->category?->nama }}</span>
                        @if ($lowStock)
                            <span class="product-badge danger">Stok Terbatas</span>
                        @endif
                        <img src="{{ $item->image_url }}" alt="{{ $item->nama }}" loading="lazy" decoding="async">
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
