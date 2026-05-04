<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggans = User::where('role', UserRole::Pelanggan)->get();
        if ($pelanggans->isEmpty()) {
            return;
        }

        // Pastikan kita punya produk
        $products = Product::with('petani')->where('is_active', true)->where('stok', '>', 0)->get();
        if ($products->isEmpty()) {
            return;
        }

        $pelanggan = $pelanggans->first();

        // Template orders: [status, days_ago, jumlah_items]
        $blueprints = [
            [OrderStatus::Pending, 0, 2],
            [OrderStatus::Dibayar, 1, 3],
            [OrderStatus::Dikirim, 3, 2],
            [OrderStatus::Selesai, 7, 4],
            [OrderStatus::Selesai, 14, 1],
            [OrderStatus::Batal,   5, 2],
        ];

        foreach ($blueprints as [$status, $daysAgo, $itemCount]) {
            // Pilih beberapa produk secara acak
            $selectedProducts = $products->random(min($itemCount, $products->count()));
            
            $subtotalProduk = 0;
            $itemsData = [];
            $stores = [];

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $subtotalProduk += $product->harga * $qty;
                
                $itemsData[] = [
                    'product_id'   => $product->id,
                    'store_id'     => $product->user_id,
                    'product_name' => $product->nama,
                    'jumlah'       => $qty,
                    'harga'        => $product->harga,
                    'berat_gram'   => $product->weight ?? 1000,
                ];

                if (!isset($stores[$product->user_id])) {
                    $stores[$product->user_id] = [
                        'store_name' => $product->petani->name ?? 'Petani',
                        'total_weight' => 0,
                    ];
                }
                $stores[$product->user_id]['total_weight'] += ($product->weight ?? 1000) * $qty;
            }

            $shippingTotal = 0;
            $shippingsData = [];

            foreach ($stores as $storeId => $storeInfo) {
                $shippingCost = rand(10, 50) * 1000; // 10k - 50k
                $shippingTotal += $shippingCost;
                
                $shippingsData[] = [
                    'store_id'        => $storeId,
                    'store_name'      => $storeInfo['store_name'],
                    'zone'            => 'local',
                    'zone_label'      => 'Lokal',
                    'base_fee'        => $shippingCost,
                    'extra_fee_per_kg'=> 0,
                    'base_weight_kg'  => 1,
                    'total_weight_kg' => ceil($storeInfo['total_weight'] / 1000),
                    'shipping_cost'   => $shippingCost,
                ];
            }

            $totalHarga = $subtotalProduk + $shippingTotal;
            $createdAt = now()->subDays($daysAgo);

            $order = Order::create([
                'user_id'           => $pelanggan->id,
                'total_harga'       => $totalHarga,
                'subtotal_produk'   => $subtotalProduk,
                'shipping_total'    => $shippingTotal,
                'status'            => $status,
                'metode_pembayaran' => 'online',
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
                'nama_penerima'     => $pelanggan->name,
                'no_hp_penerima'    => $pelanggan->no_hp ?? '081234567890',
                'alamat_pengiriman' => $pelanggan->alamat ?? 'Alamat Default',
                'shipping_province_name' => $pelanggan->province_name ?? 'Sumatera Barat',
                'shipping_city_name'     => $pelanggan->city_name ?? 'Kota Padang',
                'shipping_district_name' => $pelanggan->district_name ?? 'Padang Barat',
            ]);

            // Simpan items
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'store_id'   => $item['store_id'],
                    'jumlah'     => $item['jumlah'],
                    'harga'      => $item['harga'],
                ]);
            }

            // Simpan shippings
            foreach ($shippingsData as $shipping) {
                $shipping['order_id'] = $order->id;
                OrderShipping::create($shipping);
            }
        }
    }
}
