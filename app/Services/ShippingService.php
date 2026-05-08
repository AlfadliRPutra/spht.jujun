<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Perhitungan ongkos kirim memakai API RajaOngkir (Komerce) — multi-kurir.
 *
 * - calculateOptions() → semua opsi (kurir × service) untuk satu rute,
 *   dipakai untuk menampilkan picker di checkout.
 * - calculateShipping($selectedCode) → satu opsi (yang dipilih atau default
 *   termurah) dalam format yang kompatibel dengan order_shippings & view.
 *
 * Tidak ada fallback ke perhitungan zona lokal — bila API gagal atau mapping
 * `regencies.rajaongkir_id` belum tersedia, hasilnya `available: false` dan
 * checkout akan diblokir di view.
 */
class ShippingService
{
    /** Pseudo-zona untuk hasil dari RajaOngkir (disimpan ke order_shippings.zone). */
    public const ZONE_RAJAONGKIR = 'rajaongkir';

    public function __construct(private RajaOngkirClient $rajaOngkir = new RajaOngkirClient())
    {
    }

    /**
     * Format kode opsi yang konsisten — "courier:service", lower:upper.
     * Mis. "jne:REG", "pos:Paket Kilat Khusus" → "pos:PAKET KILAT KHUSUS".
     */
    public static function optionCode(string $courier, string $service): string
    {
        return strtolower($courier).':'.strtoupper($service);
    }

    /**
     * Daftar semua opsi service untuk rute toko → pembeli, terurut termurah.
     *
     * @return array{
     *     available: bool,
     *     reason: ?string,
     *     options: array<int, array{code:string, courier:string, courier_name:string, service:string, description:?string, cost:int, etd:?string, label:string}>,
     *     weight_kg: int,
     * }
     */
    public function calculateOptions(array $storeAddress, array $buyerAddress, float $totalWeightKg): array
    {
        $this->assertAddress('storeAddress', $storeAddress);
        $this->assertAddress('buyerAddress', $buyerAddress);

        if ($totalWeightKg <= 0) {
            throw new InvalidArgumentException('totalWeightKg harus lebih dari 0.');
        }

        $weight = (int) ceil($totalWeightKg);

        if (! $this->rajaOngkir->isConfigured()) {
            return $this->emptyOptions($weight, 'API RajaOngkir belum dikonfigurasi. Hubungi admin (set RAJAONGKIR_API_KEY).');
        }

        $originId      = $this->rajaOngkir->rajaongkirIdFor((string) $storeAddress['city_id']);
        $destinationId = $this->rajaOngkir->rajaongkirIdFor((string) $buyerAddress['city_id']);

        if (! $originId || ! $destinationId) {
            $missing = [];
            if (! $originId)      { $missing[] = 'kota toko'; }
            if (! $destinationId) { $missing[] = 'kota pengiriman'; }
            return $this->emptyOptions($weight, sprintf(
                'Mapping RajaOngkir untuk %s belum tersedia. Hubungi admin untuk melengkapi data wilayah.',
                implode(' & ', $missing),
            ));
        }

        $raw = $this->rajaOngkir->costOptions($originId, $destinationId, $weight * 1000);
        if (empty($raw)) {
            return $this->emptyOptions(
                $weight,
                'Layanan RajaOngkir tidak merespons untuk rute ini. Coba lagi beberapa menit.',
            );
        }

        $options = array_map(function (array $opt) {
            return [
                'code'         => self::optionCode($opt['code'], $opt['service']),
                'courier'      => strtolower($opt['code']),
                'courier_name' => (string) $opt['courier_name'],
                'service'      => strtoupper($opt['service']),
                'description'  => $opt['description'] ?? null,
                'cost'         => (int) $opt['cost'],
                'etd'          => $opt['etd'] ?? null,
                'label'        => sprintf('%s — %s', $this->rajaOngkir->courierName($opt['code']), strtoupper($opt['service'])),
            ];
        }, $raw);

        // Defensive sort: walaupun client.costOptions sudah sort, tetap urut
        // di sini supaya kontrak "options[0] = termurah" tidak bergantung
        // pada implementasi internal client.
        usort($options, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        return [
            'available' => true,
            'reason'    => null,
            'options'   => $options,
            'weight_kg' => $weight,
        ];
    }

    /**
     * Hitung ongkir untuk satu opsi yang dipilih (atau default termurah).
     * Format output kompatibel dengan struktur lama di view + order_shippings.
     */
    public function calculateShipping(array $storeAddress, array $buyerAddress, float $totalWeightKg, ?string $selectedCode = null): array
    {
        $opts = $this->calculateOptions($storeAddress, $buyerAddress, $totalWeightKg);
        $weight = $opts['weight_kg'];

        if (! $opts['available']) {
            return $this->unavailable($weight, (string) $opts['reason']);
        }

        $picked = null;
        if ($selectedCode) {
            foreach ($opts['options'] as $o) {
                if ($o['code'] === $selectedCode) { $picked = $o; break; }
            }
        }
        if (! $picked) {
            // Default: opsi termurah (sudah di-sort di costOptions).
            $picked = $opts['options'][0];
        }

        $msg = sprintf(
            'Pengiriman %s — Rp %s untuk %d kg%s.',
            $picked['label'],
            number_format((float) $picked['cost'], 0, ',', '.'),
            $weight,
            $picked['etd'] ? ' (estimasi '.$picked['etd'].')' : '',
        );

        return [
            'available'        => true,
            'zone'             => self::ZONE_RAJAONGKIR,
            'zone_label'       => $picked['label'],
            'courier'          => $picked['courier'],
            'service_code'     => $picked['service'],
            'option_code'      => $picked['code'],
            'base_fee'         => (int) $picked['cost'],
            'base_weight_kg'   => $weight,
            'extra_fee_per_kg' => 0,
            'total_weight_kg'  => $weight,
            'shipping_cost'    => (int) $picked['cost'],
            'message'          => $msg,
            'options'          => $opts['options'],
        ];
    }

    private function unavailable(int $weight, string $message): array
    {
        return [
            'available'        => false,
            'zone'             => null,
            'zone_label'       => 'Ongkir Tidak Tersedia',
            'courier'          => null,
            'service_code'     => null,
            'option_code'      => null,
            'base_fee'         => 0,
            'base_weight_kg'   => $weight,
            'extra_fee_per_kg' => 0,
            'total_weight_kg'  => $weight,
            'shipping_cost'    => 0,
            'message'          => $message,
            'options'          => [],
        ];
    }

    private function emptyOptions(int $weight, string $reason): array
    {
        return [
            'available' => false,
            'reason'    => $reason,
            'options'   => [],
            'weight_kg' => $weight,
        ];
    }

    private function assertAddress(string $label, array $address): void
    {
        foreach (['province_id', 'city_id', 'district_id'] as $field) {
            if (empty($address[$field])) {
                throw new InvalidArgumentException("$label.$field wajib diisi untuk perhitungan ongkir.");
            }
        }
    }
}
