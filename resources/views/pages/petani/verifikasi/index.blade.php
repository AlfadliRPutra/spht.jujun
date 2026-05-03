@php
    /** @var \App\Models\User $petani */
    /** @var string $status */
    $title  = 'Verifikasi Akun';
    $active = 'petani.verifikasi';

    $statusInfo = match ($status) {
        'verified'      => ['label' => 'Terverifikasi',       'color' => 'success', 'icon' => 'circle-check'],
        'pending'       => ['label' => 'Menunggu Review',     'color' => 'info',    'icon' => 'clock'],
        'rejected'      => ['label' => 'Ditolak',             'color' => 'danger',  'icon' => 'circle-x'],
        default         => ['label' => 'Belum Diajukan',      'color' => 'warning', 'icon' => 'alert-triangle'],
    };

    $readonly = $status === 'verified';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="small text-secondary">Status Verifikasi</div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-{{ $statusInfo['color'] }} text-white">
                                <i class="ti ti-{{ $statusInfo['icon'] }} me-1"></i> {{ $statusInfo['label'] }}
                            </span>
                            @if ($petani->verification_submitted_at)
                                <span class="text-secondary small">Diajukan {{ $petani->verification_submitted_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                    @if ($status === 'verified')
                        <i class="ti ti-shield-check text-success" style="font-size:2rem"></i>
                    @endif
                </div>
            </div>

            @if ($status === 'rejected' && $petani->verification_note)
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Pengajuan sebelumnya ditolak</div>
                    <div class="small">Catatan admin: {{ $petani->verification_note }}</div>
                    <div class="small mt-1">Silakan perbaiki data lalu ajukan ulang.</div>
                </div>
            @endif

            @if ($status === 'pending')
                <div class="alert alert-info">
                    Pengajuan Anda sedang direview admin. Anda akan diberi tahu saat selesai.
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Usaha & KTP</h3>
                </div>
                <form method="POST" action="{{ route('petani.verifikasi.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <x-form-errors title="Pengajuan verifikasi gagal" :success="false" />

                        <div class="mb-3">
                            <label class="form-label required">Nama Usaha</label>
                            <input type="text" name="nama_usaha" class="form-control"
                                   value="{{ old('nama_usaha', $petani->nama_usaha) }}"
                                   placeholder="mis. Kebun Sayur Makmur" @disabled($readonly) required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Deskripsi Usaha</label>
                            <textarea name="deskripsi_usaha" rows="3" class="form-control"
                                      placeholder="Jenis komoditas, luas lahan, pengalaman, dll."
                                      @disabled($readonly) required>{{ old('deskripsi_usaha', $petani->deskripsi_usaha) }}</textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">No. HP Aktif</label>
                                <input type="text" name="no_hp" class="form-control"
                                       value="{{ old('no_hp', $petani->no_hp) }}"
                                       placeholder="08xxxxxxxxxx" @disabled($readonly) required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">NIK (16 digit)</label>
                                <input type="text" name="nik" class="form-control"
                                       value="{{ old('nik', $petani->nik) }}"
                                       maxlength="16" pattern="\d{16}"
                                       placeholder="1234567890123456" @disabled($readonly) required>
                            </div>
                        </div>

                        <div class="mt-3 mb-3">
                            <label class="form-label required">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" class="form-control"
                                      @disabled($readonly) required>{{ old('alamat', $petani->alamat) }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label {{ $petani->ktp_image ? '' : 'required' }}">Foto KTP</label>
                            @if ($petani->ktp_image_url)
                                <div class="mb-2">
                                    <img src="{{ $petani->ktp_image_url }}" alt="KTP"
                                         id="ktp-current"
                                         style="max-width:360px;width:100%;border-radius:.5rem;border:1px solid #e7eaf0">
                                </div>
                            @endif
                            <input type="file" name="ktp_image" id="ktp-input"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                   class="form-control @error('ktp_image') is-invalid @enderror"
                                   @disabled($readonly)>
                            @error('ktp_image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="ktp-info" class="small text-secondary mt-1"></div>
                            <div class="form-text">
                                Format <strong>JPG / PNG / WEBP</strong>, maksimal <strong>4 MB</strong>.
                                Pastikan foto jelas & seluruh area KTP terlihat.
                            </div>
                        </div>
                    </div>

                    @unless ($readonly)
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-1"></i>
                                {{ $status === 'rejected' ? 'Ajukan Ulang' : ($status === 'pending' ? 'Perbarui & Kirim Ulang' : 'Ajukan Verifikasi') }}
                            </button>
                        </div>
                    @endunless
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Preview + validasi tipe & ukuran KTP sebelum submit
            (function () {
                const input   = document.getElementById('ktp-input');
                const current = document.getElementById('ktp-current');
                const info    = document.getElementById('ktp-info');
                if (!input) return;

                const ALLOWED   = ['image/jpeg','image/png','image/webp'];
                const MAX_BYTES = 4 * 1024 * 1024;

                input.addEventListener('change', () => {
                    info.textContent = '';
                    input.classList.remove('is-invalid');
                    const file = input.files?.[0];
                    if (!file) return;

                    if (!ALLOWED.includes(file.type)) {
                        input.classList.add('is-invalid');
                        info.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Format harus JPG, PNG, atau WEBP.</span>';
                        input.value = '';
                        return;
                    }
                    if (file.size > MAX_BYTES) {
                        input.classList.add('is-invalid');
                        info.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Ukuran maksimal 4 MB (file Anda '+(file.size/1024/1024).toFixed(2)+' MB).</span>';
                        input.value = '';
                        return;
                    }

                    const url = URL.createObjectURL(file);
                    if (current) {
                        current.src = url;
                    }
                    info.innerHTML = '<i class="ti ti-circle-check text-success me-1"></i>'+file.name+' &middot; '+(file.size/1024).toFixed(0)+' KB akan diunggah saat Anda klik kirim';
                });
            })();
        </script>
    @endpush
</x-layouts.app>
