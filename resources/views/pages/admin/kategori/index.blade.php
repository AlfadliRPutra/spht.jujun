@php
    $title    = 'Kategori Produk';
    $active   = 'admin.kategori';
    $kategori = \App\Models\Category::withCount('products')->orderBy('nama')->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="row row-cards">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tambah Kategori</h3></div>
                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label required">Nama Kategori</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar Kategori</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-end">Jumlah Produk</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kategori as $k)
                                <tr>
                                    <td>{{ $k->nama }}</td>
                                    <td class="text-end">{{ $k->products_count }}</td>
                                    <td class="d-flex gap-1">
                                        <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada kategori.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
