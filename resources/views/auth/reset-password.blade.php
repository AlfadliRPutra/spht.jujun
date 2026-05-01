<x-layouts.guest title="Reset Kata Sandi">
    <form class="card card-md" method="POST" action="{{ route('password.store') }}" autocomplete="off" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="card-body">
            <h2 class="h2 text-center mb-4">Atur ulang kata sandi</h2>

            <x-form-errors title="Reset kata sandi gagal" />

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi baru</label>
                <div class="input-group input-group-flat">
                    <input id="password" type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required autocomplete="new-password">
                    <span class="input-group-text">
                        <a href="#" class="link-secondary" data-password-toggle="#password" aria-label="Tampilkan kata sandi">
                            <i class="ti ti-eye" data-password-icon></i>
                        </a>
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 mt-2" id="pwd-strength" hidden>
                    <div class="flex-fill" style="height:6px;border-radius:99px;background:#eef2f7;overflow:hidden">
                        <div data-pwd-bar style="height:100%;width:0;transition:width .25s, background .25s;background:#ef4444"></div>
                    </div>
                    <span data-pwd-label class="small fw-semibold" style="min-width:80px;text-align:right">Lemah</span>
                </div>

                <ul class="list-unstyled mt-2 mb-0 small" id="pwd-rules">
                    <li data-rule="length"><i class="ti ti-circle text-secondary me-1"></i>Minimal 8 karakter</li>
                    <li data-rule="mixed"><i class="ti ti-circle text-secondary me-1"></i>Mengandung huruf besar &amp; kecil</li>
                    <li data-rule="symbol"><i class="ti ti-circle text-secondary me-1"></i>Minimal satu karakter unik (<code>!@#$%</code>)</li>
                </ul>

                @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi kata sandi</label>
                <div class="input-group input-group-flat">
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-control" required autocomplete="new-password">
                    <span class="input-group-text">
                        <a href="#" class="link-secondary" data-password-toggle="#password_confirmation" aria-label="Tampilkan kata sandi">
                            <i class="ti ti-eye" data-password-icon></i>
                        </a>
                    </span>
                </div>
                <div id="pwd-match" class="small mt-1" hidden></div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Simpan kata sandi baru</button>
            </div>
        </div>
    </form>

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

            const pwd  = document.getElementById('password');
            const conf = document.getElementById('password_confirmation');
            const meter = document.getElementById('pwd-strength');
            const match = document.getElementById('pwd-match');
            const rules = document.getElementById('pwd-rules');

            const palette = [
                ['#ef4444','Lemah'],
                ['#f97316','Cukup'],
                ['#eab308','Sedang'],
                ['#22c55e','Kuat'],
                ['#16a34a','Sangat Kuat'],
            ];

            function checkRules(p) {
                return {
                    length: p.length >= 8,
                    mixed:  /[a-z]/.test(p) && /[A-Z]/.test(p),
                    symbol: /[^A-Za-z0-9]/.test(p),
                };
            }

            function updateStrength() {
                const v = pwd.value;
                const r = checkRules(v);

                rules.querySelectorAll('[data-rule]').forEach(li => {
                    const ok = !!r[li.dataset.rule];
                    const icon = li.querySelector('i');
                    icon.className = ok
                        ? 'ti ti-circle-check text-success me-1'
                        : 'ti ti-circle text-secondary me-1';
                    li.classList.toggle('text-success', ok);
                    li.classList.toggle('text-secondary', !ok && v.length > 0);
                });

                if (!v) { meter.hidden = true; return; }
                meter.hidden = false;

                let s = 0;
                if (r.length) s++;
                if (v.length >= 12) s++;
                if (r.mixed) s++;
                if (/\d/.test(v)) s++;
                if (r.symbol) s++;
                s = Math.min(s, 4);

                const [color, label] = palette[s];
                const bar = meter.querySelector('[data-pwd-bar]');
                const lbl = meter.querySelector('[data-pwd-label]');
                bar.style.width = ((s+1)/5*100) + '%';
                bar.style.background = color;
                lbl.textContent = label;
                lbl.style.color = color;
            }

            function updateMatch() {
                if (!conf.value) { match.hidden = true; return; }
                match.hidden = false;
                if (conf.value === pwd.value) {
                    match.innerHTML = '<i class="ti ti-circle-check me-1"></i>Konfirmasi cocok';
                    match.style.color = '#16a34a';
                } else {
                    match.innerHTML = '<i class="ti ti-alert-circle me-1"></i>Belum cocok dengan kata sandi di atas';
                    match.style.color = '#dc2626';
                }
            }

            pwd?.addEventListener('input', () => { updateStrength(); updateMatch(); });
            conf?.addEventListener('input', updateMatch);
        </script>
    @endpush
</x-layouts.guest>
