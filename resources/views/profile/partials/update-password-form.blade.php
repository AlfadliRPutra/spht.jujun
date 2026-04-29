<div class="pf-head">
    <span class="ico"><i class="ti ti-lock"></i></span>
    <div class="flex-fill">
        <h3 class="title">Keamanan Akun</h3>
        <div class="sub">Ubah kata sandi untuk menjaga akun tetap aman</div>
    </div>
    @if (session('status') === 'password-updated')
        <span class="badge bg-success-lt text-success border-0"><i class="ti ti-circle-check me-1"></i>Kata sandi diperbarui</span>
    @endif
</div>

<form method="POST" action="{{ route('password.update') }}" class="pf-body" id="password-form">
    @csrf
    @method('put')

    <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
        <i class="ti ti-shield-check mt-1"></i>
        <div class="small">
            Gunakan kombinasi <strong>huruf besar &amp; kecil</strong>, <strong>angka</strong>, dan <strong>simbol</strong>
            dengan minimal <strong>8 karakter</strong>. Jangan gunakan informasi yang mudah ditebak.
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <label for="update_password_current_password" class="form-label required">Kata Sandi Saat Ini</label>
            <div class="pf-password">
                <input id="update_password_current_password" type="password" name="current_password"
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       autocomplete="current-password" placeholder="Masukkan kata sandi sekarang">
                <button type="button" class="toggle" data-toggle-pwd="update_password_current_password" tabindex="-1">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            @error('current_password', 'updatePassword') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="update_password_password" class="form-label required">Kata Sandi Baru</label>
            <div class="pf-password">
                <input id="update_password_password" type="password" name="password"
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       autocomplete="new-password" placeholder="Minimal 8 karakter">
                <button type="button" class="toggle" data-toggle-pwd="update_password_password" tabindex="-1">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2" id="pwd-strength" hidden>
                <div class="flex-fill" style="height:6px;border-radius:99px;background:#eef2f7;overflow:hidden">
                    <div id="pwd-bar" style="height:100%;width:0;transition:width .25s, background .25s;background:#ef4444"></div>
                </div>
                <span id="pwd-label" class="small fw-semibold" style="min-width:64px;text-align:right">Lemah</span>
            </div>
            @error('password', 'updatePassword') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="update_password_password_confirmation" class="form-label required">Konfirmasi Kata Sandi</label>
            <div class="pf-password">
                <input id="update_password_password_confirmation" type="password" name="password_confirmation"
                       class="form-control" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
                <button type="button" class="toggle" data-toggle-pwd="update_password_password_confirmation" tabindex="-1">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            <div id="pwd-match" class="small mt-1" hidden></div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between mt-4">
        <div class="text-secondary small">
            <i class="ti ti-info-circle me-1"></i>
            Kata sandi tidak pernah dibagikan ke pihak ketiga.
        </div>
        <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-key me-1"></i> Perbarui Kata Sandi
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Toggle eye untuk semua input password
    document.querySelectorAll('[data-toggle-pwd]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePwd);
            const icon  = btn.querySelector('i');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'ti ti-eye-off' : 'ti ti-eye';
        });
    });

    // Indikator kekuatan kata sandi (rule sederhana — informatif, bukan validasi).
    const pwd  = document.getElementById('update_password_password');
    const conf = document.getElementById('update_password_password_confirmation');
    const wrap = document.getElementById('pwd-strength');
    const bar  = document.getElementById('pwd-bar');
    const lbl  = document.getElementById('pwd-label');
    const match= document.getElementById('pwd-match');

    function score(p) {
        let s = 0;
        if (p.length >= 8)  s++;
        if (p.length >= 12) s++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++;
        if (/\d/.test(p))                       s++;
        if (/[^A-Za-z0-9]/.test(p))             s++;
        return Math.min(s, 4);
    }
    const palette = [
        ['#ef4444','Lemah'],
        ['#f97316','Cukup'],
        ['#eab308','Sedang'],
        ['#22c55e','Kuat'],
        ['#16a34a','Sangat Kuat'],
    ];
    pwd?.addEventListener('input', () => {
        const v = pwd.value;
        if (!v) { wrap.hidden = true; return; }
        wrap.hidden = false;
        const s = score(v);
        const [color, label] = palette[s];
        bar.style.width = ((s+1)/5*100) + '%';
        bar.style.background = color;
        lbl.textContent = label;
        lbl.style.color = color;
    });

    function updateMatch() {
        if (!conf || !pwd) return;
        if (!conf.value) { match.hidden = true; return; }
        match.hidden = false;
        if (conf.value === pwd.value) {
            match.innerHTML = '<i class="ti ti-circle-check me-1"></i>Konfirmasi cocok';
            match.style.color = '#16a34a';
        } else {
            match.innerHTML = '<i class="ti ti-alert-circle me-1"></i>Konfirmasi belum cocok';
            match.style.color = '#dc2626';
        }
    }
    conf?.addEventListener('input', updateMatch);
    pwd?.addEventListener('input', updateMatch);
</script>
@endpush
