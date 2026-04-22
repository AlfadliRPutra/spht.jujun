@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Hero Banner';
    $active = 'admin.hero';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title mb-0">Hero Banner Katalog ({{ $items->total() }})</h3>
                <div class="text-secondary small">Atur banner yang tampil di atas halaman katalog pelanggan.</div>
            </div>
            <a href="{{ route('admin.hero.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Tambah Banner
            </a>
        </div>

        <x-table-toolbar
            :action="route('admin.hero.index')"
            placeholder="Cari judul banner..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Status</label>
                    <select name="active" class="form-select" style="min-width:130px">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('active') === '1')>Aktif</option>
                        <option value="0" @selected(request('active') === '0')>Nonaktif</option>
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1">#</th>
                        <th style="width:140px">Gambar</th>
                        <th>Judul</th>
                        <th>CTA</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-center">Aktif</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $slide)
                        <tr>
                            <td>{{ ($items->firstItem() ?? 0) + $loop->index }}</td>
                            <td>
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}"
                                     class="rounded" style="width:120px;height:64px;object-fit:cover;background:#f6f8fa"
                                     loading="lazy" decoding="async">
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $slide->title }}</div>
                                @if ($slide->subtitle)
                                    <div class="text-secondary small text-truncate" style="max-width:340px">{{ $slide->subtitle }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($slide->cta_label)
                                    <span class="badge bg-primary-lt">{{ $slide->cta_label }}</span>
                                    <div class="text-secondary small text-truncate" style="max-width:220px">{{ $slide->cta_url }}</div>
                                @else
                                    <span class="text-secondary small">—</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $slide->sort_order }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.hero.toggle', $slide) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <label class="form-check form-switch mb-0 justify-content-center">
                                        <input type="checkbox" class="form-check-input" onchange="this.form.submit()"
                                               @checked($slide->is_active)>
                                    </label>
                                </form>
                            </td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end flex-nowrap">
                                    <a href="{{ route('admin.hero.edit', $slide) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.hero.destroy', $slide) }}"
                                          onsubmit="return confirm('Hapus banner ini?');" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                Belum ada hero banner.
                                <div class="mt-2">
                                    <a href="{{ route('admin.hero.create') }}" class="btn btn-primary btn-sm">Tambah Banner Pertama</a>
                                </div>
                            </td>
                        </tr>
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
