@php
    $title    = 'Tambah Produk';
    $active   = 'petani.produk';
    $kategori = \App\Models\Category::with('parent')
        ->whereNotIn('id', \App\Models\Category::whereNotNull('parent_id')->pluck('parent_id'))
        ->orderBy('nama')
        ->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('petani.produk.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-8">
                    <label class="form-label required">Nama Produk</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($kategori as $k)
                            <option value="{{ $k->id }}" @selected(old('category_id') == $k->id)>
                                {{ $k->parent ? $k->parent->nama.' › '.$k->nama : $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Harga</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" inputmode="numeric" name="harga"
                               value="{{ old('harga') !== null && old('harga') !== '' ? number_format((int) old('harga'), 0, ',', '.') : '' }}"
                               class="form-control js-rupiah" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok') }}" class="form-control" min="0" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" class="form-control">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                    <div class="form-text">Format JPG/PNG, maks 4 MB. Opsional — kosongkan untuk pakai placeholder.</div>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('petani.produk.index') }}" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.js-rupiah').forEach(el => {
                const fmt = v => {
                    const n = String(v).replace(/\D/g, '');
                    return n ? Number(n).toLocaleString('id-ID') : '';
                };
                el.value = fmt(el.value);
                el.addEventListener('input', () => {
                    const before = el.value.length;
                    const caret  = el.selectionStart;
                    el.value = fmt(el.value);
                    const diff = el.value.length - before;
                    el.setSelectionRange(caret + diff, caret + diff);
                });
                el.form?.addEventListener('submit', () => {
                    el.value = el.value.replace(/\./g, '');
                });
            });
        </script>
    @endpush
</x-layouts.app>
