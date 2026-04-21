@php
    $title  = 'Verifikasi Petani';
    $active = 'admin.verifikasi';
    $petani = \App\Models\User::where('role', \App\Enums\UserRole::Petani)
        ->where('is_verified', false)
        ->latest()
        ->get();
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Petani Menunggu Verifikasi</h3>
        </div>
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
                    @forelse ($petani as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->email }}</td>
                            <td>{{ $p->no_hp ?? '-' }}</td>
                            <td>{{ $p->alamat ?? '-' }}</td>
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
    </div>
</x-layouts.app>
