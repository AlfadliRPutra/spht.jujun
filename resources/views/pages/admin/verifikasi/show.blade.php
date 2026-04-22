@php
    /** @var \App\Models\User $petani */
    $title  = 'Detail Verifikasi Petani';
    $active = 'admin.verifikasi';
    $status = $petani->verificationStatus();

    [$badgeClass, $badgeLabel] = match ($status) {
        'verified'      => ['bg-green',  'Terverifikasi'],
        'pending'       => ['bg-blue',   'Menunggu Review'],
        'rejected'      => ['bg-red',    'Ditolak'],
        default         => ['bg-yellow', 'Belum Diajukan'],
    };
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="mb-3">
        <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-link text-secondary ps-0">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h3 class="card-title">Data Petani</h3>
                    <span class="badge {{ $badgeClass }} text-white">{{ $badgeLabel }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary fw-normal">Nama Lengkap</dt>
                        <dd class="col-sm-8 fw-semibold">{{ $petani->name }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Email</dt>
                        <dd class="col-sm-8">{{ $petani->email }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">No. HP</dt>
                        <dd class="col-sm-8">{{ $petani->no_hp ?? '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">NIK</dt>
                        <dd class="col-sm-8"><code>{{ $petani->nik ?? '—' }}</code></dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Nama Usaha</dt>
                        <dd class="col-sm-8">{{ $petani->nama_usaha ?? '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Deskripsi Usaha</dt>
                        <dd class="col-sm-8" style="white-space:pre-line">{{ $petani->deskripsi_usaha ?? '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Alamat</dt>
                        <dd class="col-sm-8">{{ $petani->alamat ?? '—' }}</dd>

                        <dt class="col-sm-4 text-secondary fw-normal">Terdaftar</dt>
                        <dd class="col-sm-8">{{ $petani->created_at->format('d/m/Y H:i') }}</dd>

                        @if ($petani->verification_submitted_at)
                            <dt class="col-sm-4 text-secondary fw-normal">Diajukan</dt>
                            <dd class="col-sm-8">{{ $petani->verification_submitted_at->format('d/m/Y H:i') }}</dd>
                        @endif

                        @if ($status === 'rejected' && $petani->verification_note)
                            <dt class="col-sm-4 text-secondary fw-normal">Catatan Penolakan</dt>
                            <dd class="col-sm-8 text-danger">{{ $petani->verification_note }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">Foto KTP</h3></div>
                <div class="card-body text-center" style="background:#f6f8fa">
                    @if ($petani->ktp_image_url)
                        <a href="{{ $petani->ktp_image_url }}" target="_blank">
                            <img src="{{ $petani->ktp_image_url }}" alt="KTP"
                                 style="max-width:100%;max-height:320px;border-radius:.5rem;border:1px solid #e7eaf0">
                        </a>
                        <div class="mt-2 small text-secondary">Klik untuk buka ukuran penuh</div>
                    @else
                        <div class="text-secondary py-5">KTP belum diunggah</div>
                    @endif
                </div>
            </div>

            @if ($status === 'pending')
                <div class="card border-primary">
                    <div class="card-header"><h3 class="card-title">Tindakan Verifikasi</h3></div>
                    <div class="card-body">
                        <form action="{{ route('admin.verifikasi.approve', $petani) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Setujui verifikasi {{ $petani->name }}?')">
                                <i class="ti ti-circle-check me-1"></i> Setujui Verifikasi
                            </button>
                        </form>

                        <hr class="my-3">

                        <form action="{{ route('admin.verifikasi.reject', $petani) }}" method="POST">
                            @csrf
                            <label class="form-label small">Alasan penolakan (wajib)</label>
                            <textarea name="verification_note" rows="3" class="form-control mb-2"
                                      placeholder="mis. Foto KTP buram, data NIK tidak sesuai, dll."
                                      required>{{ old('verification_note') }}</textarea>
                            @error('verification_note') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="ti ti-circle-x me-1"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($status === 'verified')
                <div class="alert alert-success">
                    <i class="ti ti-shield-check me-1"></i>
                    Petani ini sudah terverifikasi.
                </div>
            @elseif ($status === 'not_submitted')
                <div class="alert alert-warning">
                    <i class="ti ti-alert-triangle me-1"></i>
                    Petani belum mengajukan verifikasi.
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
