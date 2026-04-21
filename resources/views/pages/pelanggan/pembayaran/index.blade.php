@php($title = 'Pembayaran')
@php($active = 'pelanggan.pesanan')

<x-layouts.app :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Instruksi Pembayaran</h3></div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Silakan lakukan pembayaran sebelum <strong>{{ now()->addDay()->format('d M Y H:i') }}</strong>.
                    </div>

                    <dl class="row mb-4">
                        <dt class="col-sm-4">Metode</dt>
                        <dd class="col-sm-8">{{ ucfirst(request('metode', 'transfer')) }}</dd>

                        <dt class="col-sm-4">Nomor Tujuan</dt>
                        <dd class="col-sm-8"><code class="fs-3">1234 5678 9012</code></dd>

                        <dt class="col-sm-4">Atas Nama</dt>
                        <dd class="col-sm-8">SPHT Jujun</dd>

                        <dt class="col-sm-4">Jumlah</dt>
                        <dd class="col-sm-8"><strong>Rp 0</strong></dd>
                    </dl>

                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Upload Bukti Pembayaran</label>
                        <input type="file" name="bukti" class="form-control mb-3" accept="image/*">
                        <button class="btn btn-primary w-100">Konfirmasi Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
