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
                Kami telah mengirim tautan verifikasi ke<br>
                <strong>{{ auth()->user()->email }}</strong>
            </p>

            <p class="text-secondary mb-4">
                Untuk dapat menggunakan akun, mohon klik tautan verifikasi yang kami kirim ke alamat email di atas. Jika tidak menemukannya, periksa folder <strong>Spam</strong> atau <strong>Promosi</strong>, lalu klik tombol kirim ulang di bawah.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    Tautan verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            @endif

            <div class="d-flex flex-column flex-md-row gap-2">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-send me-1"></i> Kirim ulang tautan
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
