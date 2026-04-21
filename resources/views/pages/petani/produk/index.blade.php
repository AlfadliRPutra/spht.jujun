@php
    $title  = 'Produk Saya';
    $active = 'petani.produk';
    $produk = auth()->user()->products()->with('category')->latest()->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Produk</h3>
            <a href="{{ route('petani.produk.create') }}" class="btn btn-primary">Tambah Produk</a>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Nama</th>
                        <th>Kategori</th>
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
                            <td class="text-end">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $p->stok }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
