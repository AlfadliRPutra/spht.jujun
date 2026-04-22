@php
    /** @var \App\Models\User $petani */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Product> $products */
    $title  = 'Toko: '.$petani->name;
    $active = 'admin.toko';
@endphp

<x-layouts.app :title="$title" :active="$active">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('admin.toko.index') }}" class="btn btn-link text-secondary ps-0">
            <i class="ti ti-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Info Toko</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5 text-secondary fw-normal">Nama Petani</dt>
                        <dd class="col-7 fw-semibold">{{ $petani->name }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Email</dt>
                        <dd class="col-7">{{ $petani->email }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Nama Usaha</dt>
                        <dd class="col-7">{{ $petani->nama_usaha ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">No. HP</dt>
                        <dd class="col-7">{{ $petani->no_hp ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Alamat</dt>
                        <dd class="col-7">{{ $petani->alamat ?? '—' }}</dd>

                        <dt class="col-5 text-secondary fw-normal">Verifikasi</dt>
                        <dd class="col-7">
                            @if ($petani->is_verified)
                                <span class="badge bg-green">Terverifikasi</span>
                            @else
                                <span class="badge bg-yellow">Belum</span>
                            @endif
                        </dd>
                    </dl>

                    @if (! $petani->is_verified)
                        <a href="{{ route('admin.verifikasi.show', $petani) }}" class="btn btn-outline-primary btn-sm w-100 mt-3">
                            Tinjau Verifikasi
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Produk ({{ $products->count() }})</h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1"></th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Stok</th>
                                <th>Status</th>
                                <th class="w-1 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $p)
                                <tr>
                                    <td><img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:40px;height:40px;object-fit:cover" loading="lazy"></td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->nama }}</div>
                                        @if (! $p->is_active && $p->deactivation_reason)
                                            <div class="text-danger small"><i class="ti ti-alert-circle me-1"></i>{{ $p->deactivation_reason }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $p->category?->nama }}</td>
                                    <td class="text-end">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $p->stok }}</td>
                                    <td>
                                        @if ($p->is_active)
                                            <span class="badge bg-green">Aktif</span>
                                        @else
                                            <span class="badge bg-red">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($p->is_active)
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#deactivate-{{ $p->id }}">
                                                <i class="ti ti-ban me-1"></i> Nonaktifkan
                                            </button>

                                            <div class="modal modal-blur fade" id="deactivate-{{ $p->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <form method="POST" action="{{ route('admin.toko.product_toggle', [$petani, $p]) }}" class="modal-content">
                                                        @csrf @method('PATCH')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Nonaktifkan Produk</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-2">Produk: <strong>{{ $p->nama }}</strong></div>
                                                            <label class="form-label">Alasan (wajib)</label>
                                                            <textarea name="deactivation_reason" rows="3" class="form-control"
                                                                      placeholder="mis. Deskripsi menyesatkan, foto tidak sesuai, dll." required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('admin.toko.product_toggle', [$petani, $p]) }}" class="d-inline"
                                                  onsubmit="return confirm('Aktifkan kembali produk ini?');">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-sm btn-outline-success">
                                                    <i class="ti ti-check me-1"></i> Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-secondary py-4">Petani ini belum memiliki produk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
