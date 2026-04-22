@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var array $sortOptions */
    use App\Enums\UserRole;
    $title  = 'Data Pengguna';
    $active = 'admin.pengguna';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Pengguna Sistem ({{ $items->total() }})</h3>
            <a href="#" class="btn btn-primary">Tambah Pengguna</a>
        </div>

        <x-table-toolbar
            :action="route('admin.pengguna.index')"
            placeholder="Cari nama atau email..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Role</label>
                    <select name="role" class="form-select" style="min-width:140px">
                        <option value="">Semua</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->value }}" @selected(request('role') === $r->value)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-secondary mb-1">Verifikasi</label>
                    <select name="verified" class="form-select" style="min-width:130px">
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
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>No. HP</th>
                        <th>Verifikasi</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge bg-blue-lt">{{ $u->role->label() }}</span></td>
                            <td>{{ $u->no_hp ?? '-' }}</td>
                            <td>
                                @if ($u->is_verified)
                                    <span class="badge bg-green">Terverifikasi</span>
                                @else
                                    <span class="badge bg-yellow">Belum</span>
                                @endif
                            </td>
                            <td class="d-flex gap-1">
                                <a href="#" class="btn btn-sm btn-outline-primary">Ubah</a>
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-4">Tidak ada data.</td></tr>
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
