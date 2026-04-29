<div class="pf-head">
    <span class="ico"><i class="ti ti-alert-triangle"></i></span>
    <div class="flex-fill">
        <h3 class="title text-danger">Hapus Akun</h3>
        <div class="sub">Tindakan permanen — pastikan Anda sudah yakin</div>
    </div>
</div>

<div class="pf-body">
    <div class="danger-card">
        <div class="d-flex align-items-start gap-2">
            <i class="ti ti-alert-octagon text-danger mt-1" style="font-size:1.2rem"></i>
            <div>
                <div class="fw-semibold text-danger">Setelah dihapus, akun &amp; data berikut akan hilang:</div>
                <ul class="danger-list">
                    @if (auth()->user()->isPetani())
                        <li>Seluruh produk yang Anda jual akan dinonaktifkan.</li>
                        <li>Riwayat penjualan dan laporan transaksi tidak dapat dipulihkan.</li>
                    @elseif (auth()->user()->isPelanggan())
                        <li>Keranjang belanja, riwayat pesanan, dan alamat pengiriman.</li>
                    @else
                        <li>Akses administrator dan riwayat aktivitas pada panel admin.</li>
                    @endif
                    <li>Profil, alamat, dan data kontak yang tersimpan.</li>
                    <li>Tidak ada cara untuk mengembalikan akun setelah dihapus.</li>
                </ul>
                <div class="small text-secondary">Disarankan untuk mengunduh data penting Anda terlebih dahulu.</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modal-delete-user">
            <i class="ti ti-trash me-1"></i> Hapus Akun Saya
        </button>
    </div>
</div>

<div class="modal modal-blur fade" id="modal-delete-user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content border-0 shadow-lg">
            @csrf
            @method('delete')

            <div class="modal-body p-4">
                <div class="text-center mb-3">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2"
                         style="width:64px;height:64px;background:#fee2e2;color:#b91c1c">
                        <i class="ti ti-alert-triangle" style="font-size:1.8rem"></i>
                    </div>
                    <h3 class="mb-1">Hapus akun secara permanen?</h3>
                    <p class="text-secondary small mb-0">
                        Masukkan kata sandi Anda untuk konfirmasi.
                        Tindakan ini <strong>tidak dapat dibatalkan</strong>.
                    </p>
                </div>

                <label for="delete_password" class="form-label required">Kata Sandi</label>
                <div class="pf-password">
                    <input id="delete_password" type="password" name="password"
                           class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                           placeholder="Masukkan kata sandi" autocomplete="current-password">
                    <button type="button" class="toggle" data-toggle-pwd="delete_password" tabindex="-1">
                        <i class="ti ti-eye"></i>
                    </button>
                </div>
                @error('password', 'userDeletion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-link link-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger flex-fill">
                    <i class="ti ti-trash me-1"></i> Ya, Hapus Akun
                </button>
            </div>
        </form>
    </div>
</div>

@if ($errors->userDeletion->any())
    <script>document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('modal-delete-user')).show());</script>
@endif
