<section>
    <header>
        <h3 class="card-title text-danger">Hapus Akun</h3>
        <p class="text-secondary small">Setelah akun dihapus, semua data akan dihapus secara permanen. Unduh data penting Anda sebelum melanjutkan.</p>
    </header>

    <button type="button" class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#modal-delete-user">
        Hapus Akun
    </button>

    <div class="modal modal-blur fade" id="modal-delete-user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
                @csrf
                @method('delete')

                <div class="modal-header">
                    <h5 class="modal-title">Hapus akun permanen?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary">Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi Anda untuk konfirmasi.</p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Kata sandi</label>
                        <input id="delete_password" type="password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Kata sandi" autocomplete="current-password">
                        @error('password', 'userDeletion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger ms-auto">Ya, hapus akun</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->userDeletion->any())
        <script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('modal-delete-user')).show());</script>
    @endif
</section>
