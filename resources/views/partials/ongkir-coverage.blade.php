{{--
    Pemicu popover "daerah yang dilayani ongkir online".
    Menampilkan daftar kota/kabupaten yang sudah ter-mapping ke RajaOngkir
    (lihat App\Support\Wilayah::coveredRegions()), dikelompokkan per provinsi.

    Dipakai di halaman checkout pada toko yang ongkirnya "tidak tersedia",
    supaya pelanggan tahu rute mana yang sebenarnya bisa dihitung.

    Catatan: konten popover sengaja dibangun sebagai string lalu di-output via
    {{ }} (escaped). Browser men-decode entity di atribut sehingga Bootstrap
    (data-bs-html="true") menerima HTML yang benar. Nama wilayah berasal dari
    data seeder (tepercaya), bukan input pengguna.
--}}
@php
    $covered     = \App\Support\Wilayah::coveredRegions();
    $coveredCity = array_sum(array_map('count', $covered));
    $coveredProv = count($covered);

    $popContent = '';
    foreach ($covered as $prov => $cities) {
        $popContent .= '<div class="ongkir-cov-prov">'
            .'<div class="ongkir-cov-prov-name">'.$prov.'</div>'
            .'<div class="ongkir-cov-cities">'.implode(', ', $cities).'</div>'
            .'</div>';
    }
    if ($popContent === '') {
        $popContent = '<div class="text-secondary">Belum ada daerah yang dipetakan ke kurir. Hubungi admin untuk melengkapi data wilayah.</div>';
    }
@endphp

<button type="button"
        class="btn btn-link btn-sm p-0 align-baseline text-decoration-underline ongkir-coverage-trigger"
        data-bs-toggle="popover"
        data-bs-trigger="focus"
        data-bs-html="true"
        data-bs-placement="top"
        data-bs-custom-class="ongkir-coverage-popover"
        data-bs-title="Daerah ongkir online — {{ $coveredCity }} kota/kab. di {{ $coveredProv }} provinsi"
        data-bs-content="{{ $popContent }}">
    <i class="ti ti-map-pin me-1"></i>Lihat daerah yang dilayani
</button>
