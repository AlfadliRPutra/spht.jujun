@php
    $title  = 'Data Produk';
    $active = 'admin.produk';
    $produk = \App\Models\Product::with('category', 'petani')->latest()->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Semua Produk</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Petani</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Stok</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($produk as $p)
                        <tr>
                            <td><img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:48px;height:48px;object-fit:cover"></td>
                            <td>{{ $p->nama }}</td>
                            <td>{{ $p->category?->nama }}</td>
                            <td>{{ $p->petani?->name }}</td>
                            <td class="text-end">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $p->stok }}</td>
                            <td class="d-flex gap-1">
                                <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
