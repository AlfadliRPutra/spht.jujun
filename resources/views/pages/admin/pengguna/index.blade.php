@php
    $title    = 'Data Pengguna';
    $active   = 'admin.pengguna';
    $pengguna = \App\Models\User::latest()->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Pengguna Sistem</h3>
            <a href="#" class="btn btn-primary">Tambah Pengguna</a>
        </div>
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
                    @forelse ($pengguna as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->role->label() }}</td>
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
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
