<?php

/*
 * Dataset wilayah administratif untuk SIMULASI ongkos kirim marketplace
 * hasil tani lokal. Bukan replika BPS/Kemendagri, hanya contoh terbatas
 * agar fitur dapat diuji tanpa ketergantungan API eksternal.
 *
 * Struktur:
 *   province_id => [
 *       'name'   => string,
 *       'cities' => [
 *           city_id => [
 *               'name'      => string,
 *               'districts' => [district_id => district_name, ...],
 *           ],
 *       ],
 *   ]
 */

return [
    'provinces' => [
        '12' => [
            'name'   => 'Sumatera Utara',
            'cities' => [
                '1271' => [
                    'name'      => 'Kota Medan',
                    'districts' => [
                        '127101' => 'Medan Tuntungan',
                        '127102' => 'Medan Johor',
                        '127103' => 'Medan Amplas',
                        '127104' => 'Medan Denai',
                        '127105' => 'Medan Area',
                    ],
                ],
                '1275' => [
                    'name'      => 'Kota Binjai',
                    'districts' => [
                        '127501' => 'Binjai Selatan',
                        '127502' => 'Binjai Timur',
                        '127503' => 'Binjai Utara',
                        '127504' => 'Binjai Kota',
                        '127505' => 'Binjai Barat',
                    ],
                ],
                '1212' => [
                    'name'      => 'Kabupaten Deli Serdang',
                    'districts' => [
                        '121201' => 'Lubuk Pakam',
                        '121202' => 'Tanjung Morawa',
                        '121203' => 'Sunggal',
                        '121204' => 'Pancur Batu',
                        '121205' => 'Beringin',
                    ],
                ],
            ],
        ],
        '14' => [
            'name'   => 'Riau',
            'cities' => [
                '1471' => [
                    'name'      => 'Kota Pekanbaru',
                    'districts' => [
                        '147101' => 'Sukajadi',
                        '147102' => 'Pekanbaru Kota',
                        '147103' => 'Sail',
                        '147104' => 'Lima Puluh',
                        '147105' => 'Senapelan',
                    ],
                ],
                '1404' => [
                    'name'      => 'Kabupaten Kampar',
                    'districts' => [
                        '140401' => 'Kampar',
                        '140402' => 'Tambang',
                        '140403' => 'Bangkinang Kota',
                    ],
                ],
            ],
        ],
    ],
];
