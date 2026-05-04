@php
    /** @var \App\Models\ShippingRate[] $zones */
    $title  = 'Tarif Ongkir';
    $active = 'admin.tarif-ongkir';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <form action="{{ route('admin.tarif-ongkir.update') }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title mb-0">Tarif Ongkir per Zona</h3>
                    <div class="text-secondary small mt-1">
                        Rumus:
                        <code>shipping_cost = base_fee + max(0, total_weight_kg − base_weight_kg) × extra_fee_per_kg</code>.
                        Berat total per toko dibulatkan ke atas (per kg utuh).
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="min-width:160px">Zona</th>
                            <th style="min-width:200px">Nama / Label</th>
                            <th class="text-end" style="min-width:160px">Tarif Dasar (Rp)</th>
                            <th class="text-end" style="min-width:140px">Berat Dasar (kg)</th>
                            <th class="text-end" style="min-width:200px">Tarif per kg Ekstra (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($zones as $rate)
                            @php $zone = $rate->zone; @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold"><code>{{ $zone }}</code></div>
                                    <div class="text-secondary small">
                                        @switch($zone)
                                            @case('same_district')      Pengiriman dalam kecamatan yang sama. @break
                                            @case('same_city')          Pengiriman dalam kabupaten/kota, beda kecamatan. @break
                                            @case('same_province')      Pengiriman dalam provinsi, beda kabupaten/kota. @break
                                            @case('outside_province')   Pengiriman antar provinsi. @break
                                        @endswitch
                                    </div>
                                </td>
                                <td>
                                    <input type="text"
                                           name="rates[{{ $zone }}][label]"
                                           value="{{ old('rates.'.$zone.'.label', $rate->label) }}"
                                           class="form-control"
                                           maxlength="100" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number"
                                               name="rates[{{ $zone }}][base_fee]"
                                               value="{{ old('rates.'.$zone.'.base_fee', (int) $rate->base_fee) }}"
                                               class="form-control text-end"
                                               min="0" step="500" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number"
                                               name="rates[{{ $zone }}][base_weight_kg]"
                                               value="{{ old('rates.'.$zone.'.base_weight_kg', (int) $rate->base_weight_kg) }}"
                                               class="form-control text-end"
                                               min="0" step="1" required>
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number"
                                               name="rates[{{ $zone }}][extra_fee_per_kg]"
                                               value="{{ old('rates.'.$zone.'.extra_fee_per_kg', (int) $rate->extra_fee_per_kg) }}"
                                               class="form-control text-end"
                                               min="0" step="500" required>
                                        <span class="input-group-text">/kg</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-secondary small">
                    <i class="ti ti-info-circle me-1"></i>
                    Perubahan langsung berlaku untuk perhitungan ongkir checkout berikutnya.
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Tarif
                </button>
            </div>
        </div>
    </form>
</x-layouts.app>
