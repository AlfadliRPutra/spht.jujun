<?php

namespace App\Services;

use App\Models\Regency;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wrapper untuk endpoint RajaOngkir versi Komerce.
 *
 * Catatan: host lama `api.rajaongkir.com` sudah dimatikan setelah akuisisi.
 * API resmi sekarang: https://rajaongkir.komerce.id/api/v1/...
 *
 * Endpoint yang dipakai:
 *   POST  /api/v1/calculate/domestic-cost
 *         form: origin (id), destination (id), weight (gram), courier
 *         response: { meta, data: [ { code, name, service, description, cost, etd } ] }
 *
 *   GET   /api/v1/destination/domestic-destination?search=KEYWORD&limit=N
 *         response: { meta, data: [ { id, label, subdistrict_name, district_name,
 *                                     city_name, province_name, zip_code } ] }
 *
 * Hasil /cost di-cache (default 6 jam) per origin+destination+weight+courier.
 */
class RajaOngkirClient
{
    public function isConfigured(): bool
    {
        return ! empty($this->key());
    }

    public function key(): ?string
    {
        return config('services.rajaongkir.key') ?: null;
    }

    /**
     * Daftar kurir aktif (parsed dari config CSV). Lower-case + dedup.
     *
     * @return array<int, string>
     */
    public function couriers(): array
    {
        $raw = (string) config('services.rajaongkir.couriers', 'jne');
        $list = array_filter(array_map(
            static fn ($c) => strtolower(trim($c)),
            explode(',', $raw),
        ));
        return array_values(array_unique($list)) ?: ['jne'];
    }

    public function defaultCourier(): string
    {
        return $this->couriers()[0] ?? 'jne';
    }

    /**
     * Lookup metadata kurir (nama lengkap) dari kode pendek. Dipakai
     * untuk label UI bila API mengembalikan code saja.
     */
    public function courierName(string $code): string
    {
        return match (strtolower($code)) {
            'jne'      => 'JNE',
            'pos'      => 'POS Indonesia',
            'tiki'     => 'TIKI',
            'sicepat'  => 'SiCepat',
            'jnt'      => 'J&T Express',
            'anteraja' => 'AnterAja',
            'ninja'    => 'Ninja Xpress',
            'rpx'      => 'RPX',
            'wahana'   => 'Wahana',
            'pandu'    => 'Pandu Logistics',
            default    => strtoupper($code),
        };
    }

    public function baseUrl(): string
    {
        return rtrim((string) config('services.rajaongkir.base_url', 'https://rajaongkir.komerce.id/api/v1'), '/');
    }

    /**
     * Hitung ongkir untuk satu rute. Mengembalikan:
     *   ['cost' => int, 'service' => string, 'description' => ?string,
     *    'etd' => ?string, 'courier' => string]
     * atau null kalau API tidak tersedia / gagal / tidak menemukan service.
     */
    public function cost(string $originId, string $destinationId, int $weightGram, ?string $courier = null): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $courier = $courier ?: $this->defaultCourier();
        $weightGram = max(1000, $weightGram); // RajaOngkir minimum 1 kg

        $cacheKey = sprintf(
            'rajaongkir:cost:%s:%s:%s:%d',
            $originId, $destinationId, $courier, $weightGram,
        );

        // Cache hanya hasil sukses; failure tidak di-cache.
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::withHeaders(['key' => $this->key()])
                ->timeout((int) config('services.rajaongkir.timeout', 12))
                ->asForm()
                ->post($this->baseUrl().'/calculate/domestic-cost', [
                    'origin'      => $originId,
                    'destination' => $destinationId,
                    'weight'      => $weightGram,
                    'courier'     => $courier,
                ]);

            if (! $response->successful()) {
                Log::warning('RajaOngkir /cost non-2xx', [
                    'status' => $response->status(),
                    'body'   => mb_substr((string) $response->body(), 0, 500),
                ]);
                return null;
            }

            $parsed = $this->parseCostResponse($response, $courier);
            if (is_array($parsed)) {
                Cache::put($cacheKey, $parsed, (int) config('services.rajaongkir.cache_ttl', 21600));
            }
            return $parsed;
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir /cost exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Ambil SEMUA opsi service untuk satu kurir. Berbeda dengan cost() yang
     * hanya mengembalikan termurah, method ini mengembalikan setiap service
     * yang dikembalikan API supaya UI checkout bisa menampilkan pilihan
     * REG/OKE/YES dst. Hasil di-cache per (origin, dest, weight, courier).
     *
     * @return array<int, array{code:string, courier_name:string, service:string, description:?string, cost:int, etd:?string}>
     */
    public function costAllServices(string $originId, string $destinationId, int $weightGram, string $courier): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $courier    = strtolower($courier);
        $weightGram = max(1000, $weightGram);
        $cacheKey   = sprintf(
            'rajaongkir:cost-all:%s:%s:%s:%d',
            $originId, $destinationId, $courier, $weightGram,
        );

        // Hanya hasil sukses yang di-cache — kegagalan TIDAK di-cache supaya
        // begitu kuota Komerce reset, request berikutnya langsung hit API
        // ulang (bukan disuapi cache kosong selama 6 jam).
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::withHeaders(['key' => $this->key()])
                ->timeout((int) config('services.rajaongkir.timeout', 12))
                ->asForm()
                ->post($this->baseUrl().'/calculate/domestic-cost', [
                    'origin'      => $originId,
                    'destination' => $destinationId,
                    'weight'      => $weightGram,
                    'courier'     => $courier,
                ]);

            if (! $response->successful()) {
                Log::warning('RajaOngkir /cost non-2xx', [
                    'status'  => $response->status(),
                    'courier' => $courier,
                    'body'    => mb_substr((string) $response->body(), 0, 500),
                ]);
                return [];
            }

            $data        = (array) ($response->json('data') ?? []);
            $courierName = $this->courierName($courier);

            $options = collect($data)
                ->map(fn (array $row) => [
                    'code'         => strtolower((string) ($row['code'] ?? $courier)),
                    'courier_name' => (string) ($row['name'] ?? $courierName),
                    'service'      => strtoupper((string) ($row['service'] ?? '')),
                    'description'  => $row['description'] ?? null,
                    'cost'         => (int) ($row['cost'] ?? 0),
                    'etd'          => $row['etd'] ?? null,
                ])
                ->filter(fn ($s) => $s['cost'] > 0 && $s['service'] !== '')
                ->values()
                ->all();

            // Hanya simpan ke cache kalau ada minimal satu service valid.
            if (! empty($options)) {
                Cache::put($cacheKey, $options, (int) config('services.rajaongkir.cache_ttl', 21600));
            }

            return $options;
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir /cost exception', ['error' => $e->getMessage(), 'courier' => $courier]);
            return [];
        }
    }

    /**
     * Gabungan opsi dari beberapa kurir. Memanggil costAllServices() per
     * kurir lalu menggabungkan + sort termurah dulu. Setiap entri punya
     * `code` (jne/pos/tiki/...) sehingga bisa difilter di UI.
     *
     * @param  array<int, string>|null  $couriers  Default: list dari config.
     * @return array<int, array{code:string, courier_name:string, service:string, description:?string, cost:int, etd:?string}>
     */
    public function costOptions(string $originId, string $destinationId, int $weightGram, ?array $couriers = null): array
    {
        $couriers = $couriers !== null && ! empty($couriers) ? $couriers : $this->couriers();
        $combined = [];
        foreach ($couriers as $c) {
            foreach ($this->costAllServices($originId, $destinationId, $weightGram, $c) as $opt) {
                $combined[] = $opt;
            }
        }
        usort($combined, fn ($a, $b) => $a['cost'] <=> $b['cost']);
        return $combined;
    }

    /**
     * Cari destinasi berdasarkan kata kunci (mis. nama kota). Dipakai oleh
     * artisan sync supaya bisa memetakan regencies.rajaongkir_id.
     *
     * @return array<int, array{id:string, label:string, city_name:string, province_name:string, district_name:string}>
     */
    public function searchDestination(string $query, int $limit = 10): array
    {
        if (! $this->isConfigured() || trim($query) === '') {
            return [];
        }

        // Komerce free tier melempar 429 saat hit terlalu cepat. Coba ulang
        // dengan exponential backoff (2s → 4s → 8s) sebelum menyerah.
        $maxAttempts  = 4;
        $backoffSec   = [0, 2, 4, 8];
        $lastStatus   = 0;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            if ($backoffSec[$attempt - 1] > 0) {
                sleep($backoffSec[$attempt - 1]);
            }

            try {
                $response = Http::withHeaders(['key' => $this->key()])
                    ->timeout((int) config('services.rajaongkir.timeout', 12))
                    ->get($this->baseUrl().'/destination/domestic-destination', [
                        'search' => $query,
                        'limit'  => $limit,
                    ]);

                $lastStatus = $response->status();

                if ($response->successful()) {
                    $data = (array) ($response->json('data') ?? []);
                    return array_map(static fn ($row) => [
                        'id'            => (string) ($row['id']               ?? ''),
                        'label'         => (string) ($row['label']            ?? ''),
                        'subdistrict'   => (string) ($row['subdistrict_name'] ?? ''),
                        'district_name' => (string) ($row['district_name']   ?? ''),
                        'city_name'     => (string) ($row['city_name']       ?? ''),
                        'province_name' => (string) ($row['province_name']   ?? ''),
                    ], $data);
                }

                // 429 → retry. Selain itu (404, 401, 5xx) langsung give up
                // — retry tidak akan merubah hasil.
                if ($lastStatus !== 429) {
                    Log::warning('RajaOngkir /destination non-2xx', [
                        'status' => $lastStatus,
                        'query'  => $query,
                    ]);
                    return [];
                }
            } catch (\Throwable $e) {
                Log::warning('RajaOngkir /destination exception', [
                    'error'   => $e->getMessage(),
                    'query'   => $query,
                    'attempt' => $attempt,
                ]);
                return [];
            }
        }

        // Habis attempt tapi masih 429.
        Log::warning('RajaOngkir /destination rate limited after retries', [
            'query'    => $query,
            'attempts' => $maxAttempts,
        ]);
        return [];
    }

    /**
     * Lookup rajaongkir_id dari Regency cuid lokal. Pakai static cache
     * supaya checkout multi-toko tidak query berulang.
     */
    public function rajaongkirIdFor(string $regencyCuid): ?string
    {
        static $memo = [];
        if (array_key_exists($regencyCuid, $memo)) {
            return $memo[$regencyCuid];
        }
        $id = Regency::query()->where('id', $regencyCuid)->value('rajaongkir_id');
        return $memo[$regencyCuid] = ($id ?: null);
    }

    /**
     * Parse response /cost Komerce. Format:
     *   { data: [ { code, name, service, description, cost, etd }, ... ] }
     *
     * Pilihan service ditentukan oleh service_preference di config.
     *
     * @return array{cost:int, service:string, description:?string, etd:?string, courier:string}|null
     */
    private function parseCostResponse(Response $response, string $courier): ?array
    {
        $data = (array) ($response->json('data') ?? []);
        if (empty($data)) {
            return null;
        }

        $candidates = collect($data)
            ->map(static fn (array $row) => [
                'service'     => (string) ($row['service']     ?? ''),
                'description' => $row['description'] ?? null,
                'cost'        => (int)    ($row['cost']        ?? 0),
                'etd'         => $row['etd']         ?? null,
            ])
            ->filter(fn ($s) => $s['cost'] > 0)
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        $preference = (string) config('services.rajaongkir.service_preference', 'cheapest');
        $picked = $preference === 'cheapest'
            ? $candidates->sortBy('cost')->first()
            : ($candidates->firstWhere('service', strtoupper($preference)) ?? $candidates->sortBy('cost')->first());

        return array_merge($picked, ['courier' => strtoupper($courier)]);
    }
}
