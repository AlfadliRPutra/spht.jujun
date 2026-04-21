<section>
    <header>
        <h3 class="card-title">Ubah Kata Sandi</h3>
        <p class="text-secondary small">Pastikan Anda menggunakan kata sandi yang kuat.</p>
    </header>

    <form method="POST" action="{{ route('password.update') }}" class="mt-3">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label">Kata sandi saat ini</label>
            <input id="update_password_current_password" type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            @error('current_password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label">Kata sandi baru</label>
            <input id="update_password_password" type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password', 'updatePassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label">Konfirmasi kata sandi baru</label>
            <input id="update_password_password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
