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
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#edit-produk-{{ $p->id }}">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </button>
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

    @foreach ($items as $p)
        <div class="modal modal-blur fade" id="edit-produk-{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form method="POST" action="{{ route('petani.produk.update', $p->slug) }}" enctype="multipart/form-data" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <img src="{{ $p->image_url }}" alt="{{ $p->nama }}"
                                     class="rounded border"
                                     style="width:100%;max-width:180px;aspect-ratio:1/1;object-fit:cover;background:#f6f8fa"
                                     loading="lazy">
                                <label class="form-label small text-secondary mt-2 mb-1 d-block">Ganti Gambar</label>
                                <input type="file" name="gambar" accept="image/*" class="form-control form-control-sm">
                                <div class="form-text">Maks 4 MB</div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label required">Nama Produk</label>
                                    <input type="text" name="nama" class="form-control"
                                           value="{{ old('nama', $p->nama) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label required">Kategori</label>
                                    <select name="category_id" class="form-select" required>
                                        @foreach ($categories as $root)
                                            <option value="{{ $root->id }}" @selected($p->category_id === $root->id)>{{ $root->nama }}</option>
                                            @foreach ($root->children as $child)
                                                <option value="{{ $child->id }}" @selected($p->category_id === $child->id)>
                                                    &nbsp;&nbsp;└ {{ $child->nama }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label required">Harga (Rp)</label>
                                        <input type="number" name="harga" min="0" class="form-control"
                                               value="{{ old('harga', (int) $p->harga) }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label required">Stok</label>
                                        <input type="number" name="stok" min="0" class="form-control"
                                               value="{{ old('stok', $p->stok) }}" required>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $p->deskripsi) }}</textarea>
                                </div>
                            </div>
                        </div>

                        @if (! $p->is_active && $p->deactivation_reason)
                            <div class="alert alert-warning mt-3 mb-0">
                                <div class="fw-semibold small"><i class="ti ti-alert-triangle me-1"></i> Produk dinonaktifkan admin</div>
                                <div class="small">{{ $p->deactivation_reason }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#hapus-produk-{{ $p->id }}"
                                data-bs-dismiss="modal">
                            <i class="ti ti-trash me-1"></i> Hapus
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal modal-blur fade" id="hapus-produk-{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <form method="POST" action="{{ route('petani.produk.destroy', $p->slug) }}" class="modal-content">
                    @csrf @method('DELETE')
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size:2.5rem"></i>
                        <h3>Hapus produk ini?</h3>
                        <div class="text-secondary mb-0">
                            <strong>{{ $p->nama }}</strong> akan dihapus dari katalog.
                            Riwayat transaksi yang sudah terjadi tetap tersimpan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col"><button type="button" class="btn w-100" data-bs-dismiss="modal">Batal</button></div>
                                <div class="col"><button type="submit" class="btn btn-danger w-100">Ya, Hapus</button></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts.app>
