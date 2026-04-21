@php
    use App\Enums\UserRole;
    use App\Models\Category;
    use App\Models\Product;
    use App\Models\User;

    $stats = [
        ['label' => 'Total Pengguna',          'value' => User::count(),                                                          'icon' => 'users'],
        ['label' => 'Petani Belum Verifikasi', 'value' => User::where('role', UserRole::Petani)->where('is_verified', false)->count(), 'icon' => 'shield'],
        ['label' => 'Produk',                  'value' => Product::count(),                                                       'icon' => 'package'],
        ['label' => 'Kategori',                'value' => Category::count(),                                                      'icon' => 'category'],
    ];

    $pendingPetani = User::where('role', UserRole::Petani)->where('is_verified', false)->latest()->limit(5)->get();
    $latestProducts = Product::with('category', 'petani')->latest()->limit(5)->get();
@endphp

<div class="row row-cards mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="mb-1">Panel Admin</h2>
                <div class="text-secondary">Kelola pengguna, verifikasi petani, produk, dan kategori marketplace.</div>
            </div>
        </div>
    </div>

    @foreach ($stats as $stat)
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm"><div class="card-body">
                <div class="text-secondary">{{ $stat['label'] }}</div>
                <div class="h1 mb-0">{{ $stat['value'] }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="row row-cards">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Petani Menunggu Verifikasi</h3>
                <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-ghost-primary btn-sm">Semua →</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($pendingPetani as $p)
                    <div class="list-group-item d-flex align-items-center">
                        <div class="flex-fill">
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <div class="text-secondary small">{{ $p->email }} · Daftar {{ $p->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="badge bg-yellow">Pending</span>
                    </div>
                @empty
                    <div class="list-group-item text-center text-secondary py-4">Tidak ada yang perlu diverifikasi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Produk Terbaru</h3>
                <a href="{{ route('admin.produk.index') }}" class="btn btn-ghost-primary btn-sm">Semua →</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($latestProducts as $p)
                    <div class="list-group-item d-flex align-items-center gap-2">
                        <img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:44px;height:44px;object-fit:cover">
                        <div class="flex-fill">
                            <div class="fw-semibold">{{ $p->nama }}</div>
                            <div class="text-secondary small">{{ $p->category?->nama }} · {{ $p->petani?->name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-secondary py-4">Belum ada produk.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
