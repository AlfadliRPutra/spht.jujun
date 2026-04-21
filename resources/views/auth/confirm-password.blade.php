<x-layouts.guest title="Konfirmasi Kata Sandi">
    <form class="card card-md" method="POST" action="{{ route('password.confirm') }}" autocomplete="off" novalidate>
        @csrf
        <div class="card-body">
            <h2 class="h2 text-center mb-3">Konfirmasi kata sandi</h2>
            <p class="text-secondary mb-4">
                Area ini memerlukan konfirmasi kata sandi sebelum melanjutkan.
            </p>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autofocus autocomplete="current-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Konfirmasi</button>
            </div>
        </div>
    </form>
</x-layouts.guest>
