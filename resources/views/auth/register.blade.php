<x-layouts.guest title="Daftar">
    <form class="card card-md" method="POST" action="{{ route('register') }}" autocomplete="off" novalidate>
        @csrf
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Buat akun baru</h2>

            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Nama lengkap" required autofocus autocomplete="name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="nama@contoh.com" required autocomplete="username">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi</label>
                <div class="input-group input-group-flat">
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kata sandi" required autocomplete="new-password">
                    <span class="input-group-text">
                        <a href="#" class="link-secondary" data-bs-toggle="tooltip" aria-label="Tampilkan kata sandi">
                            <i class="ti ti-eye"></i>
                        </a>
                    </span>
                </div>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required autocomplete="new-password">
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Buat akun</button>
            </div>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">
        Sudah punya akun? <a href="{{ route('login') }}" tabindex="-1">Masuk</a>
    </div>
</x-layouts.guest>
