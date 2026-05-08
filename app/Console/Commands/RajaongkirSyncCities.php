<?php

namespace App\Console\Commands;

use App\Models\Regency;
use App\Services\RajaOngkirClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Tanam kolom `regencies.rajaongkir_id` dengan ID kota dari API Komerce.
 *
 * Komerce (penerus RajaOngkir) tidak punya endpoint dump-all-city. Jadi
 * untuk tiap regency lokal kita panggil `/destination/domestic-destination`
 * dengan kata kunci nama kota, lalu pilih hasil yang province_name-nya
 * cocok dengan provinsi regency itu (untuk disambiguasi nama yang sama
 * di beberapa provinsi, mis. Bandung Jabar vs Bandung Lampung).
 *
 * Setiap kota = 1 HTTP call. Untuk 514 regency Indonesia, command butuh
 * sekitar ~5–10 menit tergantung latency. Aman dijalankan ulang.
 *
 * Flag --force      : timpa rajaongkir_id yang sudah terisi.
 * Flag --limit=N    : batasi jumlah regency yang diproses (untuk testing).
 * Flag --sleep=MS   : delay antar request (default 150ms) supaya tidak
 *                     dianggap spam oleh API.
 */
class RajaongkirSyncCities extends Command
{
    protected $signature   = 'rajaongkir:sync-cities
                              {--force : Timpa rajaongkir_id yang sudah ada}
                              {--limit= : Batasi jumlah regency diproses}
                              {--from-users : Hanya sync kota yang dipakai oleh users/addresses (hemat kuota — disarankan untuk Komerce free tier)}
                              {--sleep=600 : Delay antar request, dalam milidetik}
                              {--max-fails=5 : Berhenti otomatis setelah N kegagalan rate-limit berturut}';
    protected $description = 'Petakan regencies.rajaongkir_id ke ID kota RajaOngkir/Komerce.';

    public function handle(RajaOngkirClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('RAJAONGKIR_API_KEY belum diset di .env. Tambahkan key terlebih dahulu.');
            return self::FAILURE;
        }

        $force      = (bool) $this->option('force');
        $fromUsers  = (bool) $this->option('from-users');
        $limit      = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;
        $sleepMicro = max(0, (int) $this->option('sleep')) * 1000;
        $maxFails   = max(1, (int) $this->option('max-fails'));

        $query = Regency::with('province');

        if ($fromUsers) {
            $cityIds = $this->relevantCityIds();
            if (empty($cityIds)) {
                $this->warn('Tidak ada city_id yang dipakai oleh users/addresses. Tidak ada yang perlu disync.');
                return self::SUCCESS;
            }
            $this->info('Mode --from-users: hanya '.count($cityIds).' kota yang dipakai user/alamat akan diproses.');
            $query->whereIn('id', $cityIds);
        }

        if (! $force) {
            $query->whereNull('rajaongkir_id');
        }
        if ($limit) {
            $query->limit($limit);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('Tidak ada regency yang perlu disinkronkan. Pakai --force untuk timpa data lama.');
            return self::SUCCESS;
        }

        $this->info("Akan memproses {$total} regency. Tiap kota = 1 panggilan API.");
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  %elapsed:6s% / %estimated:-6s%  %message%');
        $bar->setMessage('start');
        $bar->start();

        $matched           = 0;
        $unmatched         = [];
        $consecutiveFails  = 0;
        $stoppedEarly      = false;

        $query->chunkById(50, function ($chunk) use ($client, $bar, &$matched, &$unmatched, &$consecutiveFails, &$stoppedEarly, $sleepMicro, $maxFails) {
            foreach ($chunk as $regency) {
                if ($stoppedEarly) {
                    return false; // hentikan chunk loop
                }
                /** @var Regency $regency */
                $bar->setMessage(Str::limit($regency->name, 24));

                $cleanName = $this->cleanCityName($regency->name);
                $hits      = $client->searchDestination($cleanName, 10);

                if ($sleepMicro > 0) {
                    usleep($sleepMicro);
                }

                if (empty($hits)) {
                    $unmatched[] = $regency->name.' ('.$regency->province?->name.')';
                    $consecutiveFails++;
                    if ($consecutiveFails >= $maxFails) {
                        $stoppedEarly = true;
                        $bar->advance();
                        return false;
                    }
                    $bar->advance();
                    continue;
                }

                $picked = $this->pickBestMatch($hits, $regency);
                if (! $picked) {
                    $unmatched[] = $regency->name.' ('.$regency->province?->name.')';
                    $consecutiveFails++;
                    $bar->advance();
                    continue;
                }

                $regency->rajaongkir_id = (string) $picked['id'];
                $regency->save();
                $matched++;
                $consecutiveFails = 0; // reset counter setelah sukses
                $bar->advance();
            }
        });

        $bar->setMessage($stoppedEarly ? 'stopped' : 'done');
        $bar->finish();
        $this->line('');

        if ($stoppedEarly) {
            $this->error(sprintf(
                "Berhenti otomatis: %d kegagalan berturut-turut (kemungkinan rate-limit Komerce).\n"
                . 'Berhasil dipetakan %d regency sebelum berhenti. Tunggu beberapa menit/jam, lalu jalankan ulang command — yang sudah ter-set tidak akan dilewati.',
                $maxFails, $matched,
            ));
        } else {
            $this->info("Berhasil dipetakan: {$matched} dari {$total} regency.");
        }

        if (! empty($unmatched)) {
            $this->warn(count($unmatched).' regency tidak menemukan match. Beberapa di antaranya:');
            foreach (array_slice($unmatched, 0, 15) as $u) {
                $this->line('  - '.$u);
            }
            $this->line('Sisanya bisa diisi manual lewat database (kolom regencies.rajaongkir_id).');
        }

        return self::SUCCESS;
    }

    /**
     * Kumpulkan city_id unik dari users + addresses. Dipakai mode --from-users
     * supaya sync hanya menyentuh kota yang relevan dengan data aktual.
     *
     * @return array<int, string>
     */
    private function relevantCityIds(): array
    {
        $fromUsers = DB::table('users')
            ->whereNotNull('city_id')
            ->where('city_id', '!=', '')
            ->pluck('city_id');

        $fromAddresses = collect();
        if (Schema::hasTable('addresses')) {
            $fromAddresses = DB::table('addresses')
                ->whereNotNull('city_id')
                ->where('city_id', '!=', '')
                ->pluck('city_id');
        }

        return $fromUsers->merge($fromAddresses)->unique()->values()->all();
    }

    private function cleanCityName(string $name): string
    {
        // "KAB. SUNGAI PENUH" → "SUNGAI PENUH"; "KOTA BANDUNG" → "BANDUNG"
        $stripped = preg_replace('/^(kabupaten|kab\.?|kota|administrasi)\s+/iu', '', $name);

        // Sebagian data BPS punya format aneh dengan spasi tunggal antar huruf,
        // mis. "S I A K", "D U M A I". Gabung kembali jadi "SIAK", "DUMAI"
        // supaya match dengan database Komerce.
        $collapsed = preg_replace('/(?<=^|\s)(\p{L})\s(?=\p{L}\s|\p{L}$)/u', '$1', (string) $stripped);

        return trim((string) ($collapsed ?? $stripped));
    }

    /**
     * Pilih hasil terbaik: province cocok > city_name persis cocok > yang pertama.
     *
     * @param  array<int, array<string, mixed>>  $hits
     */
    private function pickBestMatch(array $hits, Regency $regency): ?array
    {
        $regencyName  = $this->normalize($regency->name);
        $provinceName = $this->normalize((string) ($regency->province?->name ?? ''));

        // Tier 1: city_name & province_name keduanya cocok.
        foreach ($hits as $h) {
            if ($this->normalize($h['city_name'] ?? '') === $regencyName
                && $this->normalize($h['province_name'] ?? '') === $provinceName) {
                return $h;
            }
        }

        // Tier 2: hanya province_name yang match.
        foreach ($hits as $h) {
            if ($this->normalize($h['province_name'] ?? '') === $provinceName) {
                return $h;
            }
        }

        // Tier 3: city_name match.
        foreach ($hits as $h) {
            if ($this->normalize($h['city_name'] ?? '') === $regencyName) {
                return $h;
            }
        }

        // Tier 4: pasrah, ambil yang pertama.
        return $hits[0] ?? null;
    }

    private function normalize(string $s): string
    {
        $s = Str::lower(trim($s));
        $s = preg_replace('/^(kabupaten|kab\.?|kota|administrasi)\s+/u', '', $s);
        $s = preg_replace('/\s+/', ' ', (string) $s);
        return (string) $s;
    }
}
