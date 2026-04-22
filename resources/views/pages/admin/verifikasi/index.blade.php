@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Verifikasi Petani';
    $active = 'admin.verifikasi';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Petani Menunggu Verifikasi ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('admin.verifikasi.index')"
            placeholder="Cari nama atau email..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage" />

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Tanggal Daftar</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->no_hp ?? '-' }}</td>
                            <td class="text-truncate" style="max-width:260px">{{ $p->alamat ?? '-' }}</td>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td class="d-flex gap-1">
                                <button class="btn btn-sm btn-success">Setujui</button>
                                <button class="btn btn-sm btn-outline-danger">Tolak</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada petani yang perlu diverifikasi.</td></tr>
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
