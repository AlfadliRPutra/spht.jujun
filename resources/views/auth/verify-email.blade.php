<x-layouts.guest title="Verifikasi Email">
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-3">Verifikasi email Anda</h2>
            <p class="text-secondary mb-4">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirim. Jika email belum diterima, kami akan kirim ulang.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    Tautan verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            @endif

            <div class="d-flex flex-column flex-md-row gap-2">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Kirim ulang tautan verifikasi</button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="flex-fill">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
