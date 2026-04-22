@php
    use App\Models\Category;
    use App\Models\Product;

    $featured = Product::with('category', 'petani')
        ->whereHas('petani', fn ($q) => $q->where('is_verified', true))
        ->where('stok', '>', 0)
        ->latest()
        ->limit(8)
        ->get();

    $categories = Category::withCount('products')->orderBy('nama')->get();

    $recentOrders = $user->orders()->latest()->limit(3)->get();
    $cartItems    = $user->cart?->items()->with('product')->get() ?? collect();
    $cartTotal    = $cartItems->sum(fn ($i) => $i->product->harga * $i->jumlah);
@endphp

@push('styles')
    <style>
        .mkt-hero {
            background: linear-gradient(135deg, #2fb344 0%, #206bc4 100%);
            color: #fff; border-radius: 1rem; padding: 2.5rem 2rem;
        }
        .mkt-hero .form-control { border: 0; }
        .cat-card { transition: transform .15s ease, box-shadow .15s ease; cursor:pointer; }
        .cat-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.06); }
        .product-card { transition: transform .15s ease, box-shadow .15s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08); }
        .product-img-wrap { overflow: hidden; }
        .product-img-wrap img { transition: transform .35s ease; }
        .product-card:hover .product-img-wrap img { transform: scale(1.06); }
    </style>
@endpush

<div class="mkt-hero mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <div class="h1 mb-1">Halo, {{ explode(' ', $user->name)[0] }} 👋</div>
            <div class="opacity-75 mb-3">Belanja hasil tani segar, langsung dari petani lokal.</div>
            <form action="{{ route('pelanggan.katalog.index') }}" method="GET" class="row g-2">
                <div class="col">
                    <input type="text" name="q" class="form-control form-control-lg" placeholder="Cari beras, bayam, jahe, ...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-dark btn-lg">Cari</button>
                </div>
            </form>
        </div>
        <div class="col-md-5 d-none d-md-block">
            <div class="d-flex justify-content-end gap-3">
                <div class="text-center">
                    <div class="h2 mb-0">{{ $featured->count() }}+</div>
                    <div class="opacity-75 small">Produk Segar</div>
                </div>
                <div class="text-center">
                    <div class="h2 mb-0">{{ $categories->count() }}</div>
                    <div class="opacity-75 small">Kategori</div>
                </div>
                <div class="text-center">
                    <div class="h2 mb-0">{{ \App\Models\User::where('role', App\Enums\UserRole::Petani)->where('is_verified', true)->count() }}</div>
                    <div class="opacity-75 small">Petani Mitra</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($cartItems->isNotEmpty())
    <div class="alert alert-info d-flex align-items-center mb-4">
        <div class="flex-fill">
            <strong>{{ $cartItems->sum('jumlah') }} item</strong> di keranjang — total Rp {{ number_format($cartTotal, 0, ',', '.') }}.
        </div>
        <a href="{{ route('pelanggan.keranjang.index') }}" class="btn btn-primary btn-sm">Lihat Keranjang</a>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="mb-0">Kategori</h3>
</div>
<div class="row row-cards mb-4">
    @foreach ($categories as $kategori)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('pelanggan.katalog.index', ['category' => $kategori->id]) }}" class="text-decoration-none text-body">
                <div class="card cat-card text-center">
                    <div class="card-body py-3">
                        <div class="avatar avatar-lg bg-primary-lt mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
                        </div>
                        <div class="fw-semibold">{{ $kategori->nama }}</div>
                        <div class="text-secondary small">{{ $kategori->products_count }} produk</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="mb-0">Produk Terbaru</h3>
    <a href="{{ route('pelanggan.katalog.index') }}" class="btn btn-ghost-primary btn-sm">Lihat semua →</a>
</div>
<div class="row row-cards mb-4">
    @foreach ($featured as $item)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 product-card">
                <div class="product-img-wrap ratio ratio-4x3 bg-light position-relative">
                    <img src="{{ $item->image_url }}" alt="{{ $item->nama }}" class="object-cover" loading="lazy">
                    <span class="badge bg-primary position-absolute m-2 top-0 start-0">{{ $item->category?->nama }}</span>
                </div>
                <div class="card-body">
                    <div class="fw-semibold text-truncate" title="{{ $item->nama }}">{{ $item->nama }}</div>
                    <div class="text-secondary small mb-2">oleh {{ $item->petani?->name }}</div>
                    <div class="h4 text-primary mb-0">Rp {{ number_format($item->harga, 0, ',', '.') }}</div>
                </div>
                <div class="card-footer pt-0 border-0 bg-transparent">
                    <a href="{{ route('pelanggan.katalog.show', $item->slug) }}" class="btn btn-outline-primary w-100 btn-sm">Lihat Detail</a>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if ($recentOrders->isNotEmpty())
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Pesanan Terakhir</h3>
            <a href="{{ route('pelanggan.pesanan.index') }}" class="btn btn-ghost-primary btn-sm">Semua pesanan →</a>
        </div>
        <div class="list-group list-group-flush">
            @foreach ($recentOrders as $order)
                <div class="list-group-item d-flex align-items-center">
                    <div class="flex-fill">
                        <div class="fw-semibold">Order #{{ $order->id }}</div>
                        <div class="text-secondary small">{{ $order->created_at->format('d M Y') }} · Rp {{ number_format($order->total_harga, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
