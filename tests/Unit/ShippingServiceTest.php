<?php

namespace Tests\Unit;

use App\Services\ShippingService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ShippingServiceTest extends TestCase
{
    private ShippingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingService();
    }

    private function address(string $city, string $district): array
    {
        return [
            'province_id' => '12',
            'city_id'     => $city,
            'district_id' => $district,
        ];
    }

    public function test_same_district_uses_base_fee_when_under_base_weight(): void
    {
        $result = $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1271', '127101'),
            3.0,
        );

        $this->assertTrue($result['available']);
        $this->assertSame('same_district', $result['zone']);
        $this->assertSame('Satu Kecamatan', $result['zone_label']);
        $this->assertSame(10000, $result['base_fee']);
        $this->assertSame(2000, $result['extra_fee_per_kg']);
        $this->assertSame(3, $result['total_weight_kg']);
        $this->assertSame(10000, $result['shipping_cost']);
    }

    public function test_same_district_adds_extra_fee_above_base_weight(): void
    {
        // 7 kg → 5 kg dasar + 2 kg ekstra × 2000 = 10000 + 4000 = 14000
        $result = $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1271', '127101'),
            7.0,
        );

        $this->assertSame(14000, $result['shipping_cost']);
        $this->assertSame(7, $result['total_weight_kg']);
    }

    public function test_same_city_different_district(): void
    {
        // 6 kg → 20000 + 1 × 3000 = 23000
        $result = $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1271', '127102'),
            6.0,
        );

        $this->assertTrue($result['available']);
        $this->assertSame('same_city', $result['zone']);
        $this->assertSame(20000, $result['base_fee']);
        $this->assertSame(3000, $result['extra_fee_per_kg']);
        $this->assertSame(23000, $result['shipping_cost']);
    }

    public function test_outside_city_blocks_shipment(): void
    {
        $result = $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1275', '127501'),
            2.0,
        );

        $this->assertFalse($result['available']);
        $this->assertSame('outside_city', $result['zone']);
        $this->assertSame(0, $result['shipping_cost']);
    }

    public function test_decimal_weight_is_rounded_up(): void
    {
        // 5.1 kg → ceil = 6 kg → same_district: 10000 + 1 × 2000 = 12000
        $result = $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1271', '127101'),
            5.1,
        );

        $this->assertSame(6, $result['total_weight_kg']);
        $this->assertSame(12000, $result['shipping_cost']);
    }

    public function test_throws_when_address_missing_required_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculateShipping(
            ['province_id' => '12'],
            $this->address('1271', '127101'),
            3.0,
        );
    }

    public function test_throws_when_weight_zero_or_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculateShipping(
            $this->address('1271', '127101'),
            $this->address('1271', '127101'),
            0,
        );
    }
}
