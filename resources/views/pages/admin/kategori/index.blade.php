@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Kategori Produk';
    $active = 'admin.kategori';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Kategori ({{ $items->total() }})</h3>
            <a href="#" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambah Kategori</a>
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
                        <th class="w-1"></th>
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
                            <td class="d-flex gap-1">
                                <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
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
</x-layouts.app>
