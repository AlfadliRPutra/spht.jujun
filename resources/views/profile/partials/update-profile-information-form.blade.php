<section>
    <header>
        <h3 class="card-title">Informasi Profil</h3>
        <p class="text-secondary small">Perbarui nama dan alamat email akun Anda.</p>
    </header>

    <form method="POST" action="{{ route('verification.send') }}" id="send-verification">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 small text-secondary">
                    Email Anda belum terverifikasi.
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">Klik di sini untuk kirim ulang email verifikasi.</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="mt-2 small text-success">Tautan verifikasi baru telah dikirim ke email Anda.</div>
                @endif
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
