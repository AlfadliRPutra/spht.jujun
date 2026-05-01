<x-layouts.guest title="Masuk">
    <form class="card card-md" method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
        @csrf
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Masuk ke akun Anda</h2>

            <x-form-errors title="Login gagal" />

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="nama@contoh.com" required autofocus autocomplete="username">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-2">
                <label for="password" class="form-label">
                    Kata sandi
                    @if (Route::has('password.request'))
                        <span class="form-label-description">
                            <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
                        </span>
                    @endif
                </label>
                <div class="input-group input-group-flat">
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Kata sandi" required autocomplete="current-password">
                    <span class="input-group-text">
                        <a href="#" class="link-secondary" data-password-toggle="#password" aria-label="Tampilkan kata sandi">
                            <i class="ti ti-eye" data-password-icon></i>
                        </a>
                    </span>
                </div>
                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mb-2">
                <label for="remember" class="form-check">
                    <input id="remember" type="checkbox" name="remember" class="form-check-input">
                    <span class="form-check-label">Ingat saya</span>
                </label>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </div>
        </div>
    </form>

    <div class="text-center text-secondary mt-3">
        Belum punya akun? <a href="#" data-bs-toggle="modal" data-bs-target="#registerRoleModal" tabindex="-1">Daftar</a>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-password-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    const input = document.querySelector(toggle.getAttribute('data-password-toggle'));
                    const icon  = toggle.querySelector('[data-password-icon]');
                    if (!input) return;
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    if (icon) {
                        icon.classList.toggle('ti-eye', !show);
                        icon.classList.toggle('ti-eye-off', show);
                    }
                });
            });
        </script>
    @endpush
</x-layouts.guest>
