<?php

namespace App\Console\Commands;

use App\Models\Regency;
use Illuminate\Console\Command;

/**
 * Tulis ulang database/seeders/RajaongkirMappingSeeder.php dari nilai
 * regencies.rajaongkir_id yang ada sekarang.
 *
 * Tujuannya membekukan hasil command rajaongkir:sync-cities ke dalam seeder
 * (ter-commit ke git) supaya mapping tidak hilang saat `migrate:fresh --seed`
 * di mesin lain, dan tim lain ikut mendapat datanya tanpa hit API lagi.
 *
 * Alur harian yang disarankan:
 *   php artisan rajaongkir:sync-cities   # tambah mapping baru (batas kuota)
 *   php artisan rajaongkir:dump-mapping  # bekukan ke seeder, lalu commit
 */
class RajaongkirDumpMapping extends Command
{
    protected $signature   = 'rajaongkir:dump-mapping';
    protected $description = 'Bekukan regencies.rajaongkir_id saat ini ke RajaongkirMappingSeeder.';

    public function handle(): int
    {
        $rows = Regency::query()
            ->whereNotNull('rajaongkir_id')
            ->orderBy('code')
            ->get(['code', 'name', 'rajaongkir_id']);

        if ($rows->isEmpty()) {
            $this->warn('Tidak ada regencies.rajaongkir_id yang terisi. Jalankan rajaongkir:sync-cities dulu.');
            return self::SUCCESS;
        }

        $count = $rows->count();
        $date  = now()->toDateString();

        $header = [
            '<?php',
            '',
            'namespace Database\Seeders;',
            '',
            'use Illuminate\Database\Seeder;',
            'use Illuminate\Support\Facades\DB;',
            '',
            '/**',
            ' * Tanam kembali regencies.rajaongkir_id dari hasil command',
            ' * rajaongkir:sync-cities. Membuat mapping ongkir tahan terhadap',
            ' * migrate:fresh --seed dan portabel antar mesin / anggota tim.',
            ' *',
            ' * Dikunci pada kode BPS (stabil); hanya meng-update baris yang sudah',
            ' * dibuat RegencySeeder, jadi WAJIB dijalankan setelahnya.',
            ' *',
            ' * JANGAN diedit manual — regenerate dengan:',
            ' *   php artisan rajaongkir:dump-mapping',
            ' *',
            " * Per {$date}: {$count} kota/kabupaten ter-mapping.",
            ' */',
            'class RajaongkirMappingSeeder extends Seeder',
            '{',
            '    public function run(): void',
            '    {',
            '        foreach ($this->data() as $code => $rajaongkirId) {',
            "            DB::table('regencies')",
            "                ->where('code', (string) \$code)",
            "                ->update(['rajaongkir_id' => (string) \$rajaongkirId]);",
            '        }',
            '    }',
            '',
            '    /** @return array<string, string>  Kode BPS => rajaongkir_id */',
            '    private function data(): array',
            '    {',
            '        return [',
        ];

        $dataLines = $rows->map(fn ($r) => sprintf(
            "            '%s' => '%s', // %s",
            $r->code,
            $r->rajaongkir_id,
            $r->name,
        ))->all();

        $footer = [
            '        ];',
            '    }',
            '}',
            '',
        ];

        $content = implode("\n", array_merge($header, $dataLines, $footer));
        $path    = database_path('seeders/RajaongkirMappingSeeder.php');
        file_put_contents($path, $content);

        $this->info("RajaongkirMappingSeeder ditulis: {$count} mapping → database/seeders/RajaongkirMappingSeeder.php");
        $this->line('Jangan lupa commit file seeder-nya.');

        return self::SUCCESS;
    }
}
