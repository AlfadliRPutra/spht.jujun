<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id'   => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds'        => env('MIDTRANS_IS_3DS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | RajaOngkir (Komerce)
    |--------------------------------------------------------------------------
    |
    | Integrasi perhitungan ongkos kirim memakai API RajaOngkir Komerce
    | (host lama `api.rajaongkir.com` sudah tidak responsif sejak akuisisi).
    |
    | Endpoint yang dipakai:
    |   POST {base_url}/calculate/domestic-cost          → /cost
    |   GET  {base_url}/destination/domestic-destination → search untuk sync
    |
    | Saat `key` kosong / mapping rajaongkir_id belum diisi / API call gagal,
    | ShippingService akan mengembalikan ongkir "tidak tersedia" (memblokir
    | checkout). Tidak ada tarif fallback lokal.
    |
    | base_url : root API (tanpa trailing slash).
    | couriers : daftar kurir CSV yang ditawarkan ke pelanggan saat checkout.
    |            Komerce mendukung jne, pos, tiki, sicepat, jnt, anteraja,
    |            ninja, dll. Tiap kurir = 1 panggilan API per rute (kuota!).
    |            Default tersedia di tier free yang aman: jne,pos,tiki.
    | service_preference : 'cheapest' (default) untuk pre-select option termurah
    |                      sebagai default; pelanggan tetap bisa ganti pakai picker.
    */
    'rajaongkir' => [
        'key'                => env('RAJAONGKIR_API_KEY'),
        'base_url'           => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'couriers'           => env('RAJAONGKIR_COURIERS', 'jne'),
        'service_preference' => env('RAJAONGKIR_SERVICE', 'cheapest'),
        'timeout'            => (int) env('RAJAONGKIR_TIMEOUT', 12),
        'cache_ttl'          => (int) env('RAJAONGKIR_CACHE_TTL', 21600),
    ],

];
