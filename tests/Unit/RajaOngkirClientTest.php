<?php

namespace Tests\Unit;

use App\Services\RajaOngkirClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RajaOngkirClientTest extends TestCase
{
    private const BASE = 'https://rajaongkir.komerce.id/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.rajaongkir.key', 'test-key');
        config()->set('services.rajaongkir.base_url', self::BASE);
        config()->set('services.rajaongkir.couriers', 'jne');
        config()->set('services.rajaongkir.service_preference', 'cheapest');
        Cache::flush();
    }

    public function test_unconfigured_client_returns_null(): void
    {
        config()->set('services.rajaongkir.key', null);
        $client = new RajaOngkirClient();
        $this->assertFalse($client->isConfigured());
        $this->assertNull($client->cost('1', '2', 1000));
        $this->assertSame([], $client->searchDestination('jakarta'));
    }

    public function test_cost_returns_cheapest_service_from_komerce_response(): void
    {
        Http::fake([
            self::BASE.'/calculate/domestic-cost' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success'],
                'data' => [
                    ['code' => 'jne', 'name' => 'JNE', 'service' => 'OKE', 'description' => 'Ongkos Kirim Ekonomis', 'cost' => 12000, 'etd' => '2-3 day'],
                    ['code' => 'jne', 'name' => 'JNE', 'service' => 'REG', 'description' => 'Layanan Reguler',       'cost' => 15000, 'etd' => '1-2 day'],
                    ['code' => 'jne', 'name' => 'JNE', 'service' => 'YES', 'description' => 'Yakin Esok Sampai',     'cost' => 25000, 'etd' => '1 day'],
                ],
            ], 200),
        ]);

        $client = new RajaOngkirClient();
        $result = $client->cost('501', '155', 3000);

        $this->assertNotNull($result);
        $this->assertSame(12000, $result['cost']);
        $this->assertSame('OKE',     $result['service']);
        $this->assertSame('JNE',     $result['courier']);
        $this->assertSame('2-3 day', $result['etd']);

        Http::assertSent(function ($request) {
            return $request->url() === self::BASE.'/calculate/domestic-cost'
                && $request->method() === 'POST'
                && $request->header('key')[0] === 'test-key'
                && $request['origin']      === '501'
                && $request['destination'] === '155'
                && (int) $request['weight'] === 3000
                && $request['courier']     === 'jne';
        });
    }

    public function test_cost_caches_response(): void
    {
        Http::fake([
            self::BASE.'/calculate/domestic-cost' => Http::response([
                'data' => [['service' => 'REG', 'cost' => 18000, 'etd' => '2 day']],
            ], 200),
        ]);

        $client = new RajaOngkirClient();
        $client->cost('1', '2', 2000);
        $client->cost('1', '2', 2000); // hit cache

        Http::assertSentCount(1);
    }

    public function test_cost_returns_null_on_5xx(): void
    {
        Http::fake([
            self::BASE.'/calculate/domestic-cost' => Http::response('Server error', 503),
        ]);

        $client = new RajaOngkirClient();
        $this->assertNull($client->cost('1', '2', 2000));
    }

    public function test_minimum_weight_1kg_enforced(): void
    {
        Http::fake([
            self::BASE.'/calculate/domestic-cost' => Http::response([
                'data' => [['service' => 'REG', 'cost' => 9000, 'etd' => '1 day']],
            ], 200),
        ]);

        $client = new RajaOngkirClient();
        $client->cost('1', '2', 500);

        Http::assertSent(fn ($request) => (int) $request['weight'] === 1000);
    }

    public function test_search_destination_returns_normalized_rows(): void
    {
        Http::fake([
            self::BASE.'/destination/domestic-destination*' => Http::response([
                'meta' => ['code' => 200, 'status' => 'success'],
                'data' => [
                    [
                        'id'               => 17549,
                        'label'            => 'JAKARTA, JAKARTA UTARA, ...',
                        'subdistrict_name' => 'KELAPA GADING',
                        'district_name'    => 'KELAPA GADING',
                        'city_name'        => 'JAKARTA UTARA',
                        'province_name'    => 'DKI JAKARTA',
                        'zip_code'         => '14240',
                    ],
                ],
            ], 200),
        ]);

        $client = new RajaOngkirClient();
        $rows   = $client->searchDestination('jakarta utara');

        $this->assertCount(1, $rows);
        $this->assertSame('17549',         $rows[0]['id']);
        $this->assertSame('JAKARTA UTARA', $rows[0]['city_name']);
        $this->assertSame('DKI JAKARTA',   $rows[0]['province_name']);

        Http::assertSent(fn ($request) =>
            str_starts_with($request->url(), self::BASE.'/destination/domestic-destination')
            && $request->method() === 'GET'
            && $request['search'] === 'jakarta utara'
        );
    }

    public function test_cost_options_aggregates_multi_courier_sorted_cheapest_first(): void
    {
        config()->set('services.rajaongkir.couriers', 'jne,pos');

        Http::fake([
            self::BASE.'/calculate/domestic-cost' => Http::sequence()
                // panggilan pertama: courier=jne
                ->push([
                    'data' => [
                        ['code' => 'jne', 'name' => 'JNE', 'service' => 'REG', 'description' => 'Reguler', 'cost' => 22000, 'etd' => '1-2 day'],
                        ['code' => 'jne', 'name' => 'JNE', 'service' => 'OKE', 'description' => 'Ekonomis', 'cost' => 19000, 'etd' => '2-3 day'],
                    ],
                ], 200)
                // panggilan kedua: courier=pos
                ->push([
                    'data' => [
                        ['code' => 'pos', 'name' => 'POS', 'service' => 'PAKET KILAT', 'description' => 'Pos', 'cost' => 17000, 'etd' => '2-4 day'],
                    ],
                ], 200),
        ]);

        $client = new RajaOngkirClient();
        $opts   = $client->costOptions('501', '155', 3000);

        $this->assertCount(3, $opts);
        // Termurah dulu
        $this->assertSame(17000, $opts[0]['cost']);
        $this->assertSame('pos', $opts[0]['code']);
        $this->assertSame(19000, $opts[1]['cost']);
        $this->assertSame(22000, $opts[2]['cost']);
    }

    public function test_search_destination_returns_empty_on_error(): void
    {
        Http::fake([
            self::BASE.'/destination/domestic-destination*' => Http::response('Forbidden', 403),
        ]);

        $client = new RajaOngkirClient();
        $this->assertSame([], $client->searchDestination('foobar'));
    }
}
