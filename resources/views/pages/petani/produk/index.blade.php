@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Produk Saya';
    $active = 'petani.produk';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Produk ({{ $items->total() }})</h3>
            <a href="{{ route('petani.produk.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambah Produk</a>
        </div>

        <x-table-toolbar
            :action="route('petani.produk.index')"
            placeholder="Cari produk..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Kategori</label>
                    <select name="category" class="form-select" style="min-width:180px">
                        <option value="">Semua</option>
                        @foreach ($categories as $root)
                            <option value="{{ $root->slug }}" @selected(request('category') === $root->slug)>{{ $root->nama }}</option>
                            @foreach ($root->children as $child)
                                <option value="{{ $child->slug }}" @selected(request('category') === $child->slug)>&nbsp;&nbsp;└ {{ $child->nama }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-secondary mb-1">Stok</label>
                    <select name="stock" class="form-select" style="min-width:130px">
                        <option value="">Semua</option>
                        <option value="low" @selected(request('stock') === 'low')>Stok Rendah (≤20)</option>
                        <option value="out" @selected(request('stock') === 'out')>Habis</option>
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Terjual</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $p)
                        <tr>
                            <td><img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:48px;height:48px;object-fit:cover" loading="lazy" decoding="async"></td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->category?->nama }}</td>
                            <td class="text-end">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="text-end">
                                @if ($p->stok <= 0)
                                    <span class="badge bg-red">Habis</span>
                                @elseif ($p->stok <= 20)
                                    <span class="badge bg-yellow">{{ $p->stok }}</span>
                                @else
                                    {{ $p->stok }}
                                @endif
                            </td>
                            <td class="text-end">{{ $p->sold_count }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $items->total() }}</strong>
            </div>
            {{ $items->links() }}
        </div>
    </div>
</x-layouts.app>
