<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Tanam kembali regencies.rajaongkir_id dari hasil command
 * rajaongkir:sync-cities. Membuat mapping ongkir tahan terhadap
 * migrate:fresh --seed dan portabel antar mesin / anggota tim.
 *
 * Dikunci pada kode BPS (stabil); hanya meng-update baris yang sudah
 * dibuat RegencySeeder, jadi WAJIB dijalankan setelahnya.
 *
 * JANGAN diedit manual — regenerate dengan:
 *   php artisan rajaongkir:dump-mapping
 *
 * Per 2026-06-13: 187 kota/kabupaten ter-mapping.
 */
class RajaongkirMappingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $code => $rajaongkirId) {
            DB::table('regencies')
                ->where('code', (string) $code)
                ->update(['rajaongkir_id' => (string) $rajaongkirId]);
        }
    }

    /** @return array<string, string>  Kode BPS => rajaongkir_id */
    private function data(): array
    {
        return [
            '1101' => '15884', // KABUPATEN SIMEULUE
            '1102' => '13257', // KABUPATEN ACEH SINGKIL
            '1103' => '9510', // KABUPATEN ACEH SELATAN
            '1104' => '9510', // KABUPATEN ACEH TENGGARA
            '1105' => '13595', // KABUPATEN ACEH TIMUR
            '1106' => '9510', // KABUPATEN ACEH TENGAH
            '1107' => '11451', // KABUPATEN ACEH BARAT
            '1108' => '10230', // KABUPATEN ACEH BESAR
            '1109' => '11657', // KABUPATEN PIDIE
            '1110' => '10770', // KABUPATEN BIREUEN
            '1111' => '14546', // KABUPATEN ACEH UTARA
            '1112' => '13016', // KABUPATEN ACEH BARAT DAYA
            '1113' => '15251', // KABUPATEN GAYO LUES
            '1114' => '13532', // KABUPATEN ACEH TAMIANG
            '1115' => '15350', // KABUPATEN NAGAN RAYA
            '1116' => '13099', // KABUPATEN ACEH JAYA
            '1117' => '15148', // KABUPATEN BENER MERIAH
            '1118' => '11657', // KABUPATEN PIDIE JAYA
            '1171' => '9524', // KOTA BANDA ACEH
            '1172' => '12933', // KOTA SABANG
            '1173' => '9590', // KOTA LANGSA
            '1174' => '9684', // KOTA LHOKSEUMAWE
            '1175' => '15915', // KOTA SUBULUSSALAM
            '1201' => '27026', // KABUPATEN NIAS
            '1202' => '29384', // KABUPATEN MANDAILING NATAL
            '1203' => '28352', // KABUPATEN TAPANULI SELATAN
            '1204' => '28011', // KABUPATEN TAPANULI TENGAH
            '1205' => '28396', // KABUPATEN TAPANULI UTARA
            '1206' => '26751', // KABUPATEN TOBA SAMOSIR
            '1207' => '41100', // KABUPATEN LABUHAN BATU
            '1208' => '41253', // KABUPATEN ASAHAN
            '1209' => '42165', // KABUPATEN SIMALUNGUN
            '1210' => '41379', // KABUPATEN DAIRI
            '1211' => '42590', // KABUPATEN KARO
            '1212' => '41534', // KABUPATEN DELI SERDANG
            '1213' => '42887', // KABUPATEN LANGKAT
            '1214' => '27458', // KABUPATEN NIAS SELATAN
            '1215' => '29028', // KABUPATEN HUMBANG HASUNDUTAN
            '1216' => '43364', // KABUPATEN PAKPAK BHARAT
            '1217' => '26751', // KABUPATEN SAMOSIR
            '1218' => '43130', // KABUPATEN SERDANG BEDAGAI
            '1219' => '43222', // KABUPATEN BATU BARA
            '1220' => '28879', // KABUPATEN PADANG LAWAS UTARA
            '1221' => '28879', // KABUPATEN PADANG LAWAS
            '1222' => '41100', // KABUPATEN LABUHAN BATU SELATAN
            '1223' => '41100', // KABUPATEN LABUHAN BATU UTARA
            '1224' => '27458', // KABUPATEN NIAS UTARA
            '1225' => '27356', // KABUPATEN NIAS BARAT
            '1271' => '28113', // KOTA SIBOLGA
            '1272' => '43822', // KOTA TANJUNG BALAI
            '1273' => '43888', // KOTA PEMATANG SIANTAR
            '1274' => '43729', // KOTA TEBING TINGGI
            '1275' => '41068', // KOTA MEDAN
            '1276' => '41911', // KOTA BINJAI
            '1277' => '28250', // KOTA PADANGSIDIMPUAN
            '1278' => '26994', // KOTA GUNUNGSITOLI
            '1301' => '48993', // KABUPATEN KEPULAUAN MENTAWAI
            '1302' => '48468', // KABUPATEN PESISIR SELATAN
            '1303' => '48836', // KABUPATEN SOLOK
            '1304' => '49233', // KABUPATEN SIJUNJUNG
            '1305' => '48267', // KABUPATEN TANAH DATAR
            '1306' => '48631', // KABUPATEN PADANG PARIAMAN
            '1307' => '49236', // KABUPATEN AGAM
            '1308' => '48977', // KABUPATEN LIMA PULUH KOTA
            '1309' => '48367', // KABUPATEN PASAMAN
            '1310' => '49140', // KABUPATEN SOLOK SELATAN
            '1311' => '48866', // KABUPATEN DHARMASRAYA
            '1312' => '49045', // KABUPATEN PASAMAN BARAT
            '1371' => '48224', // KOTA PADANG
            '1372' => '48836', // KOTA SOLOK
            '1373' => '48814', // KOTA SAWAH LUNTO
            '1374' => '48862', // KOTA PADANG PANJANG
            '1375' => '48343', // KOTA BUKITTINGGI
            '1376' => '48721', // KOTA PAYAKUMBUH
            '1377' => '48626', // KOTA PARIAMAN
            '1401' => '50897', // KABUPATEN KUANTAN SINGINGI
            '1402' => '50129', // KABUPATEN INDRAGIRI HULU
            '1403' => '50385', // KABUPATEN INDRAGIRI HILIR
            '1404' => '51038', // KABUPATEN PELALAWAN
            '1405' => '51297', // KABUPATEN S I A K
            '1406' => '49824', // KABUPATEN KAMPAR
            '1407' => '51164', // KABUPATEN ROKAN HULU
            '1408' => '49961', // KABUPATEN BENGKALIS
            '1409' => '50667', // KABUPATEN ROKAN HILIR
            '1410' => '51367', // KABUPATEN KEPULAUAN MERANTI
            '1471' => '49618', // KOTA PEKANBARU
            '1473' => '49701', // KOTA D U M A I
            '1501' => '20034', // KABUPATEN KERINCI
            '1502' => '19634', // KABUPATEN MERANGIN
            '1503' => '20520', // KABUPATEN SAROLANGUN
            '1504' => '19727', // KABUPATEN BATANG HARI
            '1505' => '20367', // KABUPATEN MUARO JAMBI
            '1506' => '20691', // KABUPATEN TANJUNG JABUNG TIMUR
            '1507' => '19378', // KABUPATEN TANJUNG JABUNG BARAT
            '1508' => '20822', // KABUPATEN TEBO
            '1509' => '19983', // KABUPATEN BUNGO
            '1571' => '19363', // KOTA JAMBI
            '1572' => '20007', // KOTA SUNGAI PENUH
            '1601' => '53149', // KABUPATEN OGAN KOMERING ULU
            '1602' => '53266', // KABUPATEN OGAN KOMERING ILIR
            '1603' => '53860', // KABUPATEN MUARA ENIM
            '1604' => '53478', // KABUPATEN LAHAT
            '1605' => '52904', // KABUPATEN MUSI RAWAS
            '1606' => '54430', // KABUPATEN MUSI BANYUASIN
            '1607' => '49382', // KABUPATEN BANYU ASIN
            '1608' => '55573', // KABUPATEN OGAN KOMERING ULU SELATAN
            '1609' => '55290', // KABUPATEN OGAN KOMERING ULU TIMUR
            '1610' => '54941', // KABUPATEN OGAN ILIR
            '1611' => '54847', // KABUPATEN EMPAT LAWANG
            '1612' => '54002', // KABUPATEN PENUKAL ABAB LEMATANG ILIR
            '1613' => '52904', // KABUPATEN MUSI RAWAS UTARA
            '1671' => '52621', // KOTA PALEMBANG
            '1672' => '54212', // KOTA PRABUMULIH
            '1673' => '54174', // KOTA PAGAR ALAM
            '1701' => '7185', // KABUPATEN BENGKULU SELATAN
            '1702' => '7075', // KABUPATEN REJANG LEBONG
            '1703' => '6666', // KABUPATEN BENGKULU UTARA
            '1704' => '7344', // KABUPATEN KAUR
            '1705' => '7979', // KABUPATEN SELUMA
            '1706' => '7776', // KABUPATEN MUKOMUKO
            '1707' => '7690', // KABUPATEN LEBONG
            '1708' => '7547', // KABUPATEN KEPAHIANG
            '1709' => '6842', // KABUPATEN BENGKULU TENGAH
            '1771' => '6641', // KOTA BENGKULU
            '1801' => '73957', // KABUPATEN LAMPUNG BARAT
            '1802' => '75957', // KABUPATEN TANGGAMUS
            '1803' => '74039', // KABUPATEN LAMPUNG SELATAN
            '1804' => '75153', // KABUPATEN LAMPUNG TIMUR
            '1805' => '74082', // KABUPATEN LAMPUNG TENGAH
            '1806' => '74373', // KABUPATEN LAMPUNG UTARA
            '1807' => '75542', // KABUPATEN WAY KANAN
            '1809' => '76311', // KABUPATEN PESAWARAN
            '1810' => '75950', // KABUPATEN PRINGSEWU
            '1811' => '75636', // KABUPATEN MESUJI
            '1812' => '75647', // KABUPATEN TULANG BAWANG BARAT
            '1813' => '48476', // KABUPATEN PESISIR BARAT
            '1871' => '74775', // KOTA BANDAR LAMPUNG
            '1872' => '74554', // KOTA METRO
            '1901' => '49361', // KABUPATEN BANGKA
            '1902' => '73825', // KABUPATEN BELITUNG
            '1903' => '49599', // KABUPATEN BANGKA BARAT
            '1904' => '49502', // KABUPATEN BANGKA TENGAH
            '1905' => '49599', // KABUPATEN BANGKA SELATAN
            '1906' => '73874', // KABUPATEN BELITUNG TIMUR
            '1971' => '49334', // KOTA PANGKAL PINANG
            '2101' => '9424', // KABUPATEN KARIMUN
            '2102' => '76604', // KABUPATEN BINTAN
            '2103' => '9295', // KABUPATEN NATUNA
            '2104' => '9212', // KABUPATEN LINGGA
            '2105' => '9329', // KABUPATEN KEPULAUAN ANAMBAS
            '2171' => '9147', // KOTA B A T A M
            '2172' => '76569', // KOTA TANJUNG PINANG
            '3101' => '17739', // KABUPATEN KEPULAUAN SERIBU
            '3171' => '17547', // KOTA JAKARTA SELATAN
            '3172' => '17674', // KOTA JAKARTA TIMUR
            '3173' => '17596', // KOTA JAKARTA PUSAT
            '3174' => '17523', // KOTA JAKARTA BARAT
            '3175' => '17644', // KOTA JAKARTA UTARA
            '3201' => '8118', // KABUPATEN BOGOR
            '3202' => '61202', // KABUPATEN SUKABUMI
            '3203' => '6157', // KABUPATEN CIANJUR
            '3204' => '4816', // KABUPATEN BANDUNG
            '3205' => '5980', // KABUPATEN GARUT
            '3206' => '77120', // KABUPATEN TASIKMALAYA
            '3207' => '77566', // KABUPATEN CIAMIS
            '3208' => '16338', // KABUPATEN KUNINGAN
            '3209' => '17117', // KABUPATEN CIREBON
            '3210' => '16711', // KABUPATEN MAJALENGKA
            '3211' => '5512', // KABUPATEN SUMEDANG
            '3212' => '16011', // KABUPATEN INDRAMAYU
            '3213' => '60440', // KABUPATEN SUBANG
            '3214' => '60248', // KABUPATEN PURWAKARTA
            '3215' => '37958', // KABUPATEN KARAWANG
            '3216' => '6532', // KABUPATEN BEKASI
            '3217' => '4816', // KABUPATEN BANDUNG BARAT
            '3218' => '77833', // KABUPATEN PANGANDARAN
            '3271' => '8118', // KOTA BOGOR
            '3272' => '61202', // KOTA SUKABUMI
            '3273' => '4816', // KOTA BANDUNG
            '3274' => '17117', // KOTA CIREBON
            '3275' => '6532', // KOTA BEKASI
            '3276' => '25986', // KOTA DEPOK
            '3277' => '5256', // KOTA CIMAHI
            '3278' => '77120', // KOTA TASIKMALAYA
            '3279' => '77541', // KOTA BANJAR
            '3301' => '19030', // KABUPATEN CILACAP
            '3302' => '72950', // KABUPATEN BANYUMAS
        ];
    }
}
