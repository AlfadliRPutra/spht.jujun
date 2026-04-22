@php
    $roleMeta = [
        'pelanggan' => ['label' => 'Pengguna', 'desc' => 'Belanja hasil tani langsung dari petani lokal.', 'icon' => 'shopping-cart', 'color' => 'primary'],
        'petani'    => ['label' => 'Petani',   'desc' => 'Jual hasil panen Anda ke pelanggan.',           'icon' => 'plant',         'color' => 'success'],
    ];
@endphp

<div class="modal modal-blur fade" id="registerRoleModal" tabindex="-1" aria-hidden="true" aria-labelledby="registerRoleModalLabel">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerRoleModalLabel">Daftar sebagai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3">Pilih jenis akun yang sesuai dengan kebutuhan Anda.</p>

                <div class="row g-3">
                    @foreach ($roleMeta as $key => $meta)
                        <div class="col-12 col-sm-6">
                            <a href="{{ route('register', ['role' => $key]) }}"
                               class="card card-link card-link-pop h-100 text-decoration-none text-reset">
                                <div class="card-body text-center py-4">
                                    <span class="avatar avatar-lg bg-{{ $meta['color'] }}-lt mb-3">
                                        <i class="ti ti-{{ $meta['icon'] }}" style="font-size:1.5rem"></i>
                                    </span>
                                    <div class="h3 mb-1">{{ $meta['label'] }}</div>
                                    <div class="text-secondary small">{{ $meta['desc'] }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-secondary small">
                    Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                </div>
            </div>
        </div>
    </div>
</div>
