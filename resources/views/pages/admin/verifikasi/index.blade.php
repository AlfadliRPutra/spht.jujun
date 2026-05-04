@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    $title  = 'Verifikasi Petani';
    $active = 'admin.verifikasi';

    $badgeFor = fn (string $s) => match ($s) {
        'verified'      => ['bg-green',  'Terverifikasi'],
        'pending'       => ['bg-blue',   'Menunggu Review'],
        'rejected'      => ['bg-red',    'Ditolak'],
        default         => ['bg-yellow', 'Belum Diajukan'],
    };
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Verifikasi Petani ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('admin.verifikasi.index')"
            placeholder="Cari nama, email, atau nama usaha..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Status</label>
                    <select name="status" class="form-select" style="min-width:170px">
                        <option value="pending"       @selected($statusFilter === 'pending')>Menunggu Review</option>
                        <option value="verified"      @selected($statusFilter === 'verified')>Terverifikasi</option>
                        <option value="rejected"      @selected($statusFilter === 'rejected')>Ditolak</option>
                        <option value="not_submitted" @selected($statusFilter === 'not_submitted')>Belum Diajukan</option>
                        <option value="all"           @selected($statusFilter === 'all')>Semua</option>
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
                        <th>NIK</th>
                        <th>KTP</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th class="w-1 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $p)
                        @php([$badgeClass, $badgeLabel] = $badgeFor($p->verificationStatus()))
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="text-secondary small">{{ $p->email }}</div>
                            </td>
                            <td>{{ $p->nama_usaha ?? '—' }}</td>
                            <td><code class="small">{{ $p->nik ?? '—' }}</code></td>
                            <td>
                                @if ($p->ktp_image_url)
                                    <a href="{{ $p->ktp_image_url }}" target="_blank">
                                        <img src="{{ $p->ktp_image_url }}" alt="KTP"
                                             style="width:60px;height:40px;object-fit:cover;border-radius:.35rem;border:1px solid #e7eaf0"
                                             loading="lazy">
                                    </a>
                                @else
                                    <span class="text-secondary small">Tidak ada</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $badgeClass }} text-white">{{ $badgeLabel }}</span></td>
                            <td class="small">
                                @if ($p->verification_submitted_at)
                                    {{ $p->verification_submitted_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.verifikasi.show', $p) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Tidak ada data.</td></tr>
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
