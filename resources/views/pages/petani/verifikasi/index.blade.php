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
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            @if ($status === 'verified')
                {{-- Akun sudah diverifikasi admin → tidak ada lagi form/dialog
                     verifikasi yang ditampilkan. Cukup celebration card dengan
                     ringkasan data toko yang tercatat. --}}
                <div class="card border-success">
                    <div class="card-status-top bg-success"></div>
                    <div class="card-body text-center py-5">
                        <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle"
                             style="width:88px;height:88px;background:#dcfce7;color:#15803d">
                            <i class="ti ti-shield-check" style="font-size:2.5rem"></i>
                        </div>
                        <h2 class="text-success mb-1">Akun Anda Terverifikasi</h2>
                        <p class="text-secondary mb-4">
                            Admin sudah memverifikasi akun Anda — produk Anda kini bisa tampil di katalog
                            dan dibeli pelanggan. Tidak ada lagi yang perlu Anda kerjakan di halaman ini.
                        </p>

                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ route('petani.produk.index') }}" class="btn btn-success">
                                <i class="ti ti-package me-1"></i> Kelola Produk
                            </a>
                            <a href="{{ route('petani.pesanan.index') }}" class="btn btn-outline-success">
                                <i class="ti ti-shopping-bag me-1"></i> Lihat Pesanan
                            </a>
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-user-circle me-1"></i> Profil Saya
                            </a>
                        </div>
                    </div>

                    @if (filled($petani->nama_usaha) || filled($petani->nik) || $petani->ktp_image_url)
                        <div class="card-footer">
                            <div class="text-secondary small fw-semibold mb-2">
                                <i class="ti ti-file-info me-1"></i>Data verifikasi tercatat
                            </div>
                            <div class="row g-3 small">
                                @if (filled($petani->nama_usaha))
                                    <div class="col-md-6">
                                        <div class="text-secondary">Nama Usaha</div>
                                        <div class="fw-semibold">{{ $petani->nama_usaha }}</div>
                                    </div>
                                @endif
                                @if (filled($petani->nik))
                                    <div class="col-md-6">
                                        <div class="text-secondary">NIK</div>
                                        <div class="fw-semibold font-monospace">
                                            {{ str_pad(substr($petani->nik, -4), strlen($petani->nik), '•', STR_PAD_LEFT) }}
                                        </div>
                                    </div>
                                @endif
                                @if ($petani->verification_submitted_at)
                                    <div class="col-md-6">
                                        <div class="text-secondary">Diajukan</div>
                                        <div>{{ $petani->verification_submitted_at->translatedFormat('d M Y') }}</div>
                                    </div>
                                @endif
                                @if ($petani->ktp_image_url)
                                    <div class="col-md-6">
                                        <div class="text-secondary">Foto KTP</div>
                                        <div><i class="ti ti-photo-check text-success me-1"></i>Tersimpan</div>
                                    </div>
                                @endif
                            </div>
                            <div class="text-secondary small mt-3">
                                <i class="ti ti-info-circle me-1"></i>
                                Data ini tersimpan untuk arsip. Untuk mengubah data toko, silakan
                                hubungi admin.
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Belum verified → tampilkan kartu status + form pengajuan / pengajuan ulang. --}}
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
                                       placeholder="mis. Kebun Sayur Makmur" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Deskripsi Usaha</label>
                                <textarea name="deskripsi_usaha" rows="3" class="form-control"
                                          placeholder="Jenis komoditas, luas lahan, pengalaman, dll."
                                          required>{{ old('deskripsi_usaha', $petani->deskripsi_usaha) }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">No. HP Aktif</label>
                                    <input type="text" name="no_hp" class="form-control"
                                           value="{{ old('no_hp', $petani->no_hp) }}"
                                           placeholder="08xxxxxxxxxx" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">NIK (16 digit)</label>
                                    <input type="text" name="nik" class="form-control"
                                           value="{{ old('nik', $petani->nik) }}"
                                           maxlength="16" pattern="\d{16}"
                                           placeholder="1234567890123456" required>
                                </div>
                            </div>

                            <div class="mt-3 mb-3">
                                <label class="form-label required">Alamat Lengkap</label>
                                <textarea name="alamat" rows="2" class="form-control"
                                          required>{{ old('alamat', $petani->alamat) }}</textarea>
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
                                       class="form-control @error('ktp_image') is-invalid @enderror">
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

                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-1"></i>
                                {{ $status === 'rejected' ? 'Ajukan Ulang' : ($status === 'pending' ? 'Perbarui & Kirim Ulang' : 'Ajukan Verifikasi') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
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
