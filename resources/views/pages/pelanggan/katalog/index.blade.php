@php
    $title    = 'Katalog Produk';
    $active   = 'pelanggan.katalog';
    $kategori = \App\Models\Category::withCount('products')->orderBy('nama')->get();

    $sort    = request('sort', 'latest');
    $sortMap = [
        'latest'     => ['created_at', 'desc'],
        'termurah'   => ['harga', 'asc'],
        'termahal'   => ['harga', 'desc'],
        'az'         => ['nama', 'asc'],
    ];
    [$sortCol, $sortDir] = $sortMap[$sort] ?? $sortMap['latest'];

    $produk = \App\Models\Product::with('category', 'petani')
        ->when(request('q'),        fn ($q, $v) => $q->where('nama', 'like', "%{$v}%"))
        ->when(request('category'), fn ($q, $v) => $q->where('category_id', $v))
        ->where('stok', '>', 0)
        ->orderBy($sortCol, $sortDir)
        ->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    @push('styles')
        <style>
            .hero-catalog {
                background: linear-gradient(135deg, #2fb344 0%, #206bc4 100%);
                color: #fff; border-radius: .75rem; padding: 2rem 1.5rem;
            }
            .hero-catalog .form-control, .hero-catalog .form-select {
                border: 0; background: rgba(255,255,255,.95);
            }
            .product-card { transition: transform .15s ease, box-shadow .15s ease; }
            .product-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08); }
            .product-img-wrap { overflow: hidden; border-top-left-radius: calc(var(--tblr-border-radius) - 1px); border-top-right-radius: calc(var(--tblr-border-radius) - 1px); }
            .product-img-wrap img { transition: transform .35s ease; }
            .product-card:hover .product-img-wrap img { transform: scale(1.06); }
            .category-pill { white-space: nowrap; }
            .stock-bar { height: 4px; border-radius: 2px; background: #e9ecef; overflow: hidden; }
            .stock-bar > span { display:block; height:100%; background:#2fb344; }
        </style>
    @endpush

    <div class="hero-catalog mb-3">
        <div class="row align-items-center g-3">
            <div class="col-md-5">
                <div class="h2 mb-1">Produk Pertanian Segar</div>
                <div class="opacity-75">Langsung dari petani lokal, untuk keluargamu.</div>
            </div>
            <div class="col-md-7">
                <form class="row g-2">
                    <div class="col-8">
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Cari produk..." value="{{ request('q') }}">
                    </div>
                    <div class="col-4">
                        <button class="btn btn-dark btn-lg w-100">Cari</button>
                    </div>
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <input type="hidden" name="sort"     value="{{ request('sort') }}">
                </form>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 overflow-auto pb-2 mb-3">
        <a href="{{ route('pelanggan.katalog.index', ['q' => request('q'), 'sort' => request('sort')]) }}"
           class="btn btn-sm category-pill {{ request('category') ? 'btn-outline-primary' : 'btn-primary' }}">
            Semua
        </a>
        @foreach ($kategori as $k)
            <a href="{{ route('pelanggan.katalog.index', ['q' => request('q'), 'sort' => request('sort'), 'category' => $k->id]) }}"
               class="btn btn-sm category-pill {{ request('category') == $k->id ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $k->nama }} <span class="badge bg-secondary-lt ms-1">{{ $k->products_count }}</span>
            </a>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-secondary">Menampilkan <strong>{{ $produk->count() }}</strong> produk</div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="q"        value="{{ request('q') }}">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <label class="text-secondary small mb-0">Urutkan:</label>
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:160px">
                <option value="latest"   @selected($sort === 'latest')>Terbaru</option>
                <option value="termurah" @selected($sort === 'termurah')>Harga: Termurah</option>
                <option value="termahal" @selected($sort === 'termahal')>Harga: Termahal</option>
                <option value="az"       @selected($sort === 'az')>Nama: A-Z</option>
            </select>
        </form>
    </div>

    <div class="row row-cards">
        @forelse ($produk as $item)
            @php($lowStock = $item->stok <= 20)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 product-card">
                    <div class="product-img-wrap ratio ratio-4x3 bg-light position-relative">
                        <img src="{{ $item->image_url }}" alt="{{ $item->nama }}" class="object-cover" loading="lazy">
                        <span class="badge bg-primary position-absolute m-2 top-0 start-0">{{ $item->category?->nama }}</span>
                        @if ($lowStock)
                            <span class="badge bg-red position-absolute m-2 top-0 end-0">Stok Terbatas</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h3 class="card-title mb-1 text-truncate" title="{{ $item->nama }}">{{ $item->nama }}</h3>
                        <div class="text-secondary small mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
                            {{ $item->petani?->name }}
                        </div>
                        <div class="h3 text-primary mb-2">Rp {{ number_format($item->harga, 0, ',', '.') }}</div>
                        <div class="stock-bar mb-1"><span style="width: {{ min(100, $item->stok) }}%"></span></div>
                        <div class="small text-secondary">Stok: {{ $item->stok }}</div>
                    </div>
                    <div class="card-footer bg-transparent pt-0 border-0 d-flex gap-2">
                        <a href="{{ route('pelanggan.katalog.show') }}" class="btn btn-outline-primary flex-fill">Detail</a>
                        <button class="btn btn-primary flex-fill" title="Masukkan keranjang">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty">
                    <p class="empty-title">Produk tidak ditemukan</p>
                    <p class="empty-subtitle text-secondary">Coba ubah kata kunci atau pilih kategori lain.</p>
                    <div class="empty-action">
                        <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-primary">Reset Pencarian</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</x-layouts.app>
