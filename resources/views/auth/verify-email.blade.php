@php
    $ttl = (int) config('auth.verification.expire', 60);
@endphp

<x-layouts.guest title="Verifikasi Email">
    <div class="card card-md">
        <div class="card-body">
            <div class="text-center mb-3">
                <span class="avatar avatar-lg bg-primary-lt">
                    <i class="ti ti-mail-check fs-1"></i>
                </span>
            </div>
            <h2 class="h2 text-center mb-2">Verifikasi email Anda</h2>
            <p class="text-secondary text-center mb-4">
                Kami telah mengirim email ke<br>
                <strong>{{ auth()->user()->email }}</strong>
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success alert-dismissible">
                    <i class="ti ti-circle-check me-1"></i>
                    Tautan &amp; kode verifikasi baru telah dikirim. Periksa inbox / folder spam.
                    <a class="btn-close" data-bs-dismiss="alert"></a>
                </div>
            @endif

            <x-form-errors title="Verifikasi gagal" :success="false" />

            <div class="alert alert-info">
                <div class="fw-semibold mb-1"><i class="ti ti-info-circle me-1"></i>Pilih salah satu cara verifikasi:</div>
                <ol class="mb-0 ps-3 small">
                    <li><strong>Klik tombol "Verifikasi Email"</strong> di dalam email yang kami kirim, atau</li>
                    <li><strong>Salin kode 6-digit</strong> dari email lalu masukkan di form di bawah.</li>
                </ol>
            </div>

            {{-- Form verifikasi pakai kode --}}
            <form method="POST" action="{{ route('verification.code') }}" autocomplete="off" novalidate>
                @csrf
                <label for="code" class="form-label">Kode Verifikasi 6-digit</label>
                <div class="input-group input-group-lg mb-1">
                    <span class="input-group-text"><i class="ti ti-key"></i></span>
                    <input id="code" name="code" type="text" inputmode="numeric" pattern="\d{6}" maxlength="6"
                           value="{{ old('code') }}"
                           class="form-control text-center fw-bold @error('code') is-invalid @enderror"
                           style="letter-spacing:.5em;font-size:1.4rem"
                           placeholder="••••••" required autofocus>
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-text mb-3">
                    Kode kedaluwarsa dalam {{ $ttl }} menit. Bisa diminta ulang lewat tombol "Kirim ulang" di bawah.
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="ti ti-shield-check me-1"></i> Verifikasi dengan Kode
                </button>
            </form>

            <div class="hr-text hr-text-center hr-text-spaceless mb-3"><span class="text-secondary">atau</span></div>

            <div class="d-flex flex-column flex-md-row gap-2">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="ti ti-send me-1"></i> Kirim ulang email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">Keluar</button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Auto-format kode: hanya angka, auto-submit saat 6 digit terisi
            const code = document.getElementById('code');
            code?.addEventListener('input', () => {
                code.value = code.value.replace(/\D/g, '').slice(0, 6);
                if (code.value.length === 6) {
                    code.form?.submit();
                }
            });
        </script>
    @endpush
</x-layouts.guest>
