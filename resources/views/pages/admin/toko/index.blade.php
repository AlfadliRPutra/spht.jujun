@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Manajemen Toko / Petani';
    $active = 'admin.toko';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">Toko / Petani ({{ $items->total() }})</h3>
                <div class="text-secondary small">Lihat produk tiap petani. Admin dapat menonaktifkan produk bila ada kejanggalan.</div>
            </div>
        </div>

        <x-table-toolbar
            :action="route('admin.toko.index')"
            placeholder="Cari nama petani, usaha, atau email..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Verifikasi</label>
                    <select name="verified" class="form-select" style="min-width:150px">
                        <option value="">Semua</option>
                        <option value="1" @selected(request('verified') === '1')>Terverifikasi</option>
                        <option value="0" @selected(request('verified') === '0')>Belum</option>
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Petani</th>
                        <th>Nama Usaha</th>
                        <th>Verifikasi</th>
                        <th class="text-center">Total Produk</th>
                        <th class="text-center">Aktif / Nonaktif</th>
                        <th>Bergabung</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="text-secondary small">{{ $p->email }}</div>
                            </td>
                            <td>{{ $p->nama_usaha ?? '—' }}</td>
                            <td>
                                @if ($p->is_verified)
                                    <span class="badge bg-green">Terverifikasi</span>
                                @else
                                    <span class="badge bg-yellow">Belum</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $p->products_count }}</td>
                            <td class="text-center">
                                <span class="badge bg-green-lt">{{ $p->active_products_count }}</span>
                                <span class="text-secondary">/</span>
                                <span class="badge bg-red-lt">{{ $p->inactive_products_count }}</span>
                            </td>
                            <td class="small">{{ $p->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.toko.show', $p) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada petani.</td></tr>
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
