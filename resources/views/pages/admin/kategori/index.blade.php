@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $kategoriItems */
    /** @var \Illuminate\Pagination\LengthAwarePaginator $subItems */
    /** @var \Illuminate\Support\Collection $allParents */
    /** @var string $tab */
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
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a href="{{ route('admin.kategori.index', ['tab' => 'main']) }}"
                       class="nav-link {{ $tab === 'main' ? 'active' : '' }}">
                        <i class="ti ti-category-2 me-1"></i> Kategori
                        <span class="badge bg-secondary-lt ms-1">{{ $kategoriItems->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.kategori.index', ['tab' => 'sub']) }}"
                       class="nav-link {{ $tab === 'sub' ? 'active' : '' }}">
                        <i class="ti ti-list-tree me-1"></i> Sub Kategori
                        <span class="badge bg-secondary-lt ms-1">{{ $subItems->total() }}</span>
                    </a>
                </li>
                <li class="ms-auto">
                    @if ($tab === 'main')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kategori-create">
                            <i class="ti ti-plus me-1"></i> Tambah Kategori
                        </button>
                    @else
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#sub-create">
                            <i class="ti ti-plus me-1"></i> Tambah Sub Kategori
                        </button>
                    @endif
                </li>
            </ul>
        </div>

        @if ($tab === 'main')
            {{-- ── Tab Kategori Utama ───────────────────────────────────────── --}}
            <div class="card-body border-bottom py-2">
                <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
                    <input type="hidden" name="tab" value="main">
                    <div class="flex-grow-1" style="min-width:240px">
                        <label class="form-label small text-secondary mb-1">Cari Kategori</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="Nama kategori...">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                    @if (request('q'))
                        <a href="{{ route('admin.kategori.index', ['tab' => 'main']) }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th class="text-center">Urutan</th>
                            <th class="text-end">Sub Kategori</th>
                            <th class="text-end">Produk</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kategoriItems as $k)
                            <tr>
                                <td>
                                    @if ($k->icon)<i class="ti ti-{{ $k->icon }} me-1 text-secondary"></i>@endif
                                    {{ $k->nama }}
                                </td>
                                <td><code class="small">{{ $k->slug }}</code></td>
                                <td class="text-center">{{ $k->sort_order }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.kategori.index', ['tab' => 'sub', 'parent' => $k->slug]) }}"
                                       class="badge bg-blue-lt text-decoration-none">
                                        {{ $k->sub_categories_count }} sub
                                    </a>
                                </td>
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
                    Menampilkan <strong>{{ $kategoriItems->firstItem() ?? 0 }}</strong> - <strong>{{ $kategoriItems->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $kategoriItems->total() }}</strong>
                </div>
                {{ $kategoriItems->links() }}
            </div>
        @else
            {{-- ── Tab Sub Kategori ─────────────────────────────────────────── --}}
            <div class="card-body border-bottom py-2">
                <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
                    <input type="hidden" name="tab" value="sub">
                    <div class="flex-grow-1" style="min-width:240px">
                        <label class="form-label small text-secondary mb-1">Cari Sub Kategori</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                               placeholder="Nama sub kategori...">
                    </div>
                    <div style="min-width:200px">
                        <label class="form-label small text-secondary mb-1">Kategori Induk</label>
                        <select name="parent" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach ($allParents as $p)
                                <option value="{{ $p->slug }}" @selected(request('parent') === $p->slug)>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
                    @if (request('q') || request('parent'))
                        <a href="{{ route('admin.kategori.index', ['tab' => 'sub']) }}" class="btn btn-sm btn-link">Reset</a>
                    @endif
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Kategori Induk</th>
                            <th>Nama Sub</th>
                            <th>Slug</th>
                            <th class="text-center">Urutan</th>
                            <th class="text-end">Produk</th>
                            <th class="w-1 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subItems as $s)
                            <tr>
                                <td><span class="badge bg-blue-lt">{{ $s->category?->nama ?? '—' }}</span></td>
                                <td>{{ $s->nama }}</td>
                                <td><code class="small">{{ $s->slug }}</code></td>
                                <td class="text-center">{{ $s->sort_order }}</td>
                                <td class="text-end">{{ $s->products_count }}</td>
                                <td class="text-end">
                                    <div class="btn-list justify-content-end flex-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal" data-bs-target="#sub-edit-{{ $s->id }}">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal" data-bs-target="#sub-delete-{{ $s->id }}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada sub kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="text-secondary small">
                    Menampilkan <strong>{{ $subItems->firstItem() ?? 0 }}</strong> - <strong>{{ $subItems->lastItem() ?? 0 }}</strong>
                    dari <strong>{{ $subItems->total() }}</strong>
                </div>
                {{ $subItems->links() }}
            </div>
        @endif
    </div>

    {{-- ── Modals: Kategori Utama ─────────────────────────────────────────── --}}
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

    @foreach ($kategoriItems as $k)
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
                                <div class="text-danger small mt-1">Masih dipakai {{ $k->products_count }} produk — hapus akan gagal.</div>
                            @endif
                            @if ($k->sub_categories_count > 0)
                                <div class="text-danger small mt-1">Masih punya {{ $k->sub_categories_count }} sub kategori — hapus akan gagal.</div>
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

    {{-- ── Modals: Sub Kategori ───────────────────────────────────────────── --}}
    <div class="modal modal-blur fade" id="sub-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('admin.kategori.sub.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sub Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Kategori Induk</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Pilih kategori induk</option>
                            @foreach ($allParents as $p)
                                <option value="{{ $p->id }}" @selected(old('category_id') == $p->id)>{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nama Sub Kategori</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                    </div>
                    <div>
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', 0) }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($subItems as $s)
        <div class="modal modal-blur fade" id="sub-edit-{{ $s->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.kategori.sub.update', $s->slug) }}" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Sub Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Kategori Induk</label>
                            <select name="category_id" class="form-select" required>
                                @foreach ($allParents as $p)
                                    <option value="{{ $p->id }}" @selected(old('category_id', $s->category_id) == $p->id)>{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Nama Sub Kategori</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $s->nama) }}" required>
                        </div>
                        <div>
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" min="0" class="form-control" value="{{ old('sort_order', $s->sort_order) }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal modal-blur fade" id="sub-delete-{{ $s->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <form method="POST" action="{{ route('admin.kategori.sub.destroy', $s->slug) }}" class="modal-content">
                    @csrf @method('DELETE')
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size:2.5rem"></i>
                        <h3>Hapus sub kategori?</h3>
                        <div class="text-secondary">
                            <strong>{{ $s->nama }}</strong> akan dihapus permanen.
                            @if ($s->products_count > 0)
                                <div class="text-danger small mt-1">Masih dipakai {{ $s->products_count }} produk — hapus akan gagal.</div>
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
