<?php

namespace Tests\Unit;

use App\Services\RajaOngkirClient;
use App\Services\ShippingService;
use InvalidArgumentException;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    private function address(string $province, string $city, string $district): array
    {
        return [
            'province_id' => $province,
            'city_id'     => $city,
            'district_id' => $district,
        ];
    }

    public function test_throws_when_address_missing_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);

        $svc = new ShippingService($client);
        $svc->calculateShipping(
            ['province_id' => '12'],
            $this->address('12', '1271', '127101'),
            3.0,
        );
    }

    public function test_throws_when_weight_zero_or_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);

        $svc = new ShippingService($client);
        $svc->calculateShipping(
            $this->address('12', '1271', '127101'),
            $this->address('12', '1271', '127101'),
            0,
        );
    }

    public function test_returns_unavailable_when_api_unconfigured(): void
    {
        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(false);
        $client->expects($this->never())->method('cost');

        $svc = new ShippingService($client);
        $r = $svc->calculateShipping(
            $this->address('12', '1271', '127101'),
            $this->address('12', '1271', '127101'),
            3.0,
        );

        $this->assertFalse($r['available']);
        $this->assertSame(0, $r['shipping_cost']);
        $this->assertStringContainsString('RAJAONGKIR_API_KEY', $r['message']);
    }

    public function test_returns_unavailable_when_mapping_missing(): void
    {
        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);
        $client->method('rajaongkirIdFor')->willReturn(null);
        $client->expects($this->never())->method('cost');

        $svc = new ShippingService($client);
        $r = $svc->calculateShipping(
            $this->address('p1', 'c1', 'd1'),
            $this->address('p2', 'c2', 'd2'),
            3.0,
        );

        $this->assertFalse($r['available']);
        $this->assertSame(0, $r['shipping_cost']);
        $this->assertStringContainsString('Mapping RajaOngkir', $r['message']);
    }

    public function test_returns_unavailable_when_api_returns_no_options(): void
    {
        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);
        $client->method('rajaongkirIdFor')->willReturnCallback(fn ($id) => '999');
        $client->method('costOptions')->willReturn([]);

        $svc = new ShippingService($client);
        $r = $svc->calculateShipping(
            $this->address('p1', 'c1', 'd1'),
            $this->address('p2', 'c2', 'd2'),
            3.0,
        );

        $this->assertFalse($r['available']);
        $this->assertStringContainsString('tidak merespons', $r['message']);
    }

    public function test_uses_rajaongkir_with_cheapest_option_by_default(): void
    {
        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);
        $client->method('rajaongkirIdFor')->willReturnCallback(fn ($id) => match ($id) {
            'cuid-jakarta' => '152',
            'cuid-bandung' => '23',
            default        => null,
        });
        $client->method('courierName')->willReturnCallback(fn ($c) => match (strtolower($c)) {
            'jne'  => 'JNE',
            'pos'  => 'POS Indonesia',
            default => strtoupper($c),
        });
        $client->expects($this->once())
            ->method('costOptions')
            ->with('152', '23', 3000)
            ->willReturn([
                ['code' => 'jne', 'courier_name' => 'JNE',           'service' => 'REG', 'description' => 'Reguler', 'cost' => 22000, 'etd' => '1-2 day'],
                ['code' => 'pos', 'courier_name' => 'POS Indonesia', 'service' => 'PAKET KILAT', 'description' => 'Pos', 'cost' => 18000, 'etd' => '2-4 day'],
                ['code' => 'jne', 'courier_name' => 'JNE',           'service' => 'OKE', 'description' => 'Ekonomis', 'cost' => 19000, 'etd' => '2-3 day'],
            ]);

        $svc = new ShippingService($client);
        $r = $svc->calculateShipping(
            $this->address('prov-jkt', 'cuid-jakarta', 'dist-jkt'),
            $this->address('prov-bdg', 'cuid-bandung', 'dist-bdg'),
            3.0,
        );

        $this->assertTrue($r['available']);
        $this->assertSame(ShippingService::ZONE_RAJAONGKIR, $r['zone']);
        // Termurah dari list (POS 18000) yang dipilih sebagai default.
        $this->assertSame(18000, $r['shipping_cost']);
        $this->assertSame('pos', $r['courier']);
        $this->assertSame('PAKET KILAT', $r['service_code']);
        $this->assertCount(3, $r['options']);
    }

    public function test_uses_selected_option_when_specified(): void
    {
        $client = $this->createMock(RajaOngkirClient::class);
        $client->method('isConfigured')->willReturn(true);
        $client->method('rajaongkirIdFor')->willReturn('999');
        $client->method('courierName')->willReturnCallback(fn ($c) => strtoupper($c));
        $client->method('costOptions')->willReturn([
            ['code' => 'jne', 'courier_name' => 'JNE', 'service' => 'REG', 'description' => null, 'cost' => 22000, 'etd' => '1-2 day'],
            ['code' => 'pos', 'courier_name' => 'POS', 'service' => 'KILAT', 'description' => null, 'cost' => 18000, 'etd' => '2-4 day'],
        ]);

        $svc = new ShippingService($client);
        $r = $svc->calculateShipping(
            $this->address('p', 'c1', 'd'),
            $this->address('p', 'c2', 'd'),
            3.0,
            'jne:REG', // pilih JNE walau bukan termurah
        );

        $this->assertTrue($r['available']);
        $this->assertSame('jne', $r['courier']);
        $this->assertSame('REG', $r['service_code']);
        $this->assertSame(22000, $r['shipping_cost']);
    }
}
