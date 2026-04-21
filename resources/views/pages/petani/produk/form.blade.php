@php
    $title    = 'Tambah Produk';
    $active   = 'petani.produk';
    $kategori = \App\Models\Category::orderBy('nama')->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-body">
            <form action="#" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-8">
                    <label class="form-label required">Nama Produk</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Harga</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="harga" class="form-control" min="0" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Stok</label>
                    <input type="number" name="stok" class="form-control" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" class="form-control"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('petani.produk.index') }}" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
