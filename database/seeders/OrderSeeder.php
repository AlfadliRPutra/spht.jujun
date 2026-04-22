<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = User::where('role', UserRole::Pelanggan)->first();
        $products  = Product::where('is_active', true)->where('stok', '>', 0)->inRandomOrder()->get();

        if (! $pelanggan || $products->count() < 3) {
            return;
        }

        // [status, metode_pembayaran, created_days_ago, [products_index => jumlah]]
        $blueprints = [
            [OrderStatus::Pending, 'transfer', 0, [[0, 2], [1, 1]]],
            [OrderStatus::Dibayar, 'ewallet',  1, [[2, 3]]],
            [OrderStatus::Dibayar, 'transfer', 2, [[3, 1], [4, 2]]],
            [OrderStatus::Dikirim, 'transfer', 3, [[5, 2]]],
            [OrderStatus::Selesai, 'cod',      7, [[6, 4]]],
            [OrderStatus::Batal,   'transfer', 5, [[7, 1]]],
        ];

        foreach ($blueprints as [$status, $metode, $daysAgo, $lines]) {
            $total = 0;
            $items = [];

            foreach ($lines as [$idx, $jumlah]) {
                $product = $products->get($idx);
                if (! $product) {
                    continue;
                }
                $items[] = ['product' => $product, 'jumlah' => $jumlah];
                $total += (float) $product->harga * $jumlah;
            }

            if (empty($items)) {
                continue;
            }

            $order = Order::create([
                'user_id'           => $pelanggan->id,
                'total_harga'       => $total,
                'status'            => $status,
                'metode_pembayaran' => $metode,
                'created_at'        => now()->subDays($daysAgo),
                'updated_at'        => now()->subDays($daysAgo),
            ]);

            foreach ($items as $line) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $line['product']->id,
                    'jumlah'     => $line['jumlah'],
                    'harga'      => $line['product']->harga,
                ]);
            }
        }
    }
}
