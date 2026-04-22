@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var \Illuminate\Support\Collection $roots */
    $title  = 'Kategori Produk';
    $active = 'admin.kategori';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}<a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}<a class="btn-close" data-bs-dismiss="alert"></a>
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
            <h3 class="card-title mb-0">Daftar Kategori ({{ $items->total() }})</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kategori-create">
                <i class="ti ti-plus me-1"></i> Tambah Kategori
            </button>
        </div>

        <x-table-toolbar
            :action="route('admin.kategori.index')"
            placeholder="Cari kategori..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Level</label>
                    <select name="level" class="form-select" style="min-width:140px">
                        <option value="">Semua</option>
                        <option value="root" @selected(request('level') === 'root')>Kategori Utama</option>
                        <option value="sub" @selected(request('level') === 'sub')>Sub Kategori</option>
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Parent</th>
                        <th>Slug</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-end">Jumlah Produk</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $k)
                        <tr>
                            <td>
                                @if ($k->icon)<i class="ti ti-{{ $k->icon }} me-1 text-secondary"></i>@endif
                                {{ $k->nama }}
                            </td>
                            <td>
                                @if ($k->parent)
                                    <span class="badge bg-blue-lt">{{ $k->parent->nama }}</span>
                                @else
                                    <span class="badge bg-green-lt">Utama</span>
                                @endif
                            </td>
                            <td><code class="small">{{ $k->slug }}</code></td>
                            <td class="text-center">{{ $k->sort_order }}</td>
                            <td class="text-end">{{ $k->products_count }}</td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#kategori-edit-{{ $k->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#kategori-delete-{{ $k->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada kategori.</td></tr>
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

    {{-- Modal: Tambah Kategori --}}
    <div class="modal modal-blur fade" id="kategori-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.kategori.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Kategori</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent (opsional)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— Kategori Utama —</option>
                            @foreach ($roots as $r)
                                <option value="{{ $r->id }}" @selected(old('parent_id') == $r->id)>{{ $r->nama }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Kosongkan untuk membuat kategori utama.</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-8">
                            <label class="form-label">Icon (Tabler)</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon') }}" placeholder="mis. salad, apple">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', 0) }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($items as $k)
        {{-- Modal: Ubah Kategori --}}
        <div class="modal modal-blur fade" id="kategori-edit-{{ $k->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.kategori.update', $k->slug) }}" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Kategori</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $k->nama) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent</label>
                            <select name="parent_id" class="form-select">
                                <option value="">— Kategori Utama —</option>
                                @foreach ($roots as $r)
                                    @if ($r->id !== $k->id)
                                        <option value="{{ $r->id }}" @selected(old('parent_id', $k->parent_id) == $r->id)>{{ $r->nama }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-8">
                                <label class="form-label">Icon</label>
                                <input type="text" name="icon" class="form-control" value="{{ old('icon', $k->icon) }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $k->sort_order) }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Konfirmasi Hapus --}}
        <div class="modal modal-blur fade" id="kategori-delete-{{ $k->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <form method="POST" action="{{ route('admin.kategori.destroy', $k->slug) }}" class="modal-content">
                    @csrf @method('DELETE')
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size:2.5rem"></i>
                        <h3>Hapus kategori?</h3>
                        <div class="text-secondary">
                            <strong>{{ $k->nama }}</strong> akan dihapus permanen.
                            @if ($k->products_count > 0)
                                <div class="text-danger small mt-1">Kategori ini masih punya {{ $k->products_count }} produk — hapus akan gagal.</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100"><div class="row">
                            <div class="col"><button type="button" class="btn w-100" data-bs-dismiss="modal">Batal</button></div>
                            <div class="col"><button type="submit" class="btn btn-danger w-100">Ya, Hapus</button></div>
                        </div></div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts.app>
