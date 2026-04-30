<x-layouts.guest title="Reset Kata Sandi">
    <form class="card card-md" method="POST" action="{{ route('password.store') }}" autocomplete="off" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="card-body">
            <h2 class="h2 text-center mb-4">Atur ulang kata sandi</h2>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi baru</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                <small class="form-hint">
                    Minimal 8 karakter, kombinasi huruf besar &amp; kecil, serta minimal satu karakter unik (mis. <code>!@#$%</code>).
                </small>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Simpan kata sandi baru</button>
            </div>
        </div>
    </form>
</x-layouts.guest>
