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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pengguna-create">
                <i class="ti ti-plus me-1"></i> Tambah Pengguna
            </button>
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
                        <th class="w-1 text-end">Aksi</th>
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
                                    <span class="badge bg-green-lt">Terverifikasi</span>
                                @else
                                    <span class="badge bg-yellow-lt">Belum</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-list justify-content-end flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#pengguna-edit-{{ $u->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#pengguna-delete-{{ $u->id }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
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

    {{-- Modal: Tambah Pengguna --}}
    <div class="modal modal-blur fade" id="pengguna-create" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="{{ route('admin.pengguna.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Password (min. 8 karakter)</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select name="role" class="form-select" required>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->value }}" @selected(old('role') === $r->value)>{{ $r->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-control">{{ old('alamat') }}</textarea>
                    </div>
                    <label class="form-check form-switch mb-0">
                        <input type="hidden" name="is_verified" value="0">
                        <input type="checkbox" name="is_verified" value="1" class="form-check-input" @checked(old('is_verified'))>
                        <span class="form-check-label">Tandai terverifikasi</span>
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-user-plus me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($items as $u)
        {{-- Modal: Ubah Pengguna --}}
        <div class="modal modal-blur fade" id="pengguna-edit-{{ $u->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <form method="POST" action="{{ route('admin.pengguna.update', $u) }}" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $u->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $u->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password baru</label>
                            <input type="password" name="password" class="form-control" minlength="8" placeholder="Kosongkan jika tidak diubah">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label required">Role</label>
                                <select name="role" class="form-select" required>
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->value }}" @selected(old('role', $u->role->value) === $r->value)>{{ $r->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. HP</label>
                                <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $u->no_hp) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" rows="2" class="form-control">{{ old('alamat', $u->alamat) }}</textarea>
                        </div>
                        <label class="form-check form-switch mb-0">
                            <input type="hidden" name="is_verified" value="0">
                            <input type="checkbox" name="is_verified" value="1" class="form-check-input" @checked(old('is_verified', $u->is_verified))>
                            <span class="form-check-label">Terverifikasi</span>
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Hapus Pengguna --}}
        <div class="modal modal-blur fade" id="pengguna-delete-{{ $u->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <form method="POST" action="{{ route('admin.pengguna.destroy', $u) }}" class="modal-content">
                    @csrf @method('DELETE')
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size:2.5rem"></i>
                        <h3>Hapus pengguna?</h3>
                        <div class="text-secondary">
                            <strong>{{ $u->name }}</strong> ({{ $u->email }}) akan dihapus permanen.
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
