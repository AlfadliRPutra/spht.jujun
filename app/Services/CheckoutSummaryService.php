<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Cart;
use App\Models\User;

/**
 * Mengelompokkan isi keranjang per toko (petani), menghitung berat & subtotal
 * tiap toko, lalu memanggil ShippingService untuk menghitung ongkir per toko.
 *
 * Hasilnya berbentuk struktur ringkasan checkout yang langsung dipakai oleh
 * view Blade maupun controller pembayaran.
 */
class CheckoutSummaryService
{
    public function __construct(private ShippingService $shipping) {}

    /**
     * @param  array|null         $buyerAddressOverride  Snapshot alamat dari Address yang dipilih
     *                                                   di halaman checkout. Jika null, pakai default
     *                                                   user (User::addressSnapshot()).
     * @param  PaymentMethod|null $paymentMethod         Online pakai ongkir normal;
     *                                                   Pickup memaksa ongkir 0 untuk semua toko.
     * @return array{
     *     stores: array<int, array<string, mixed>>,
     *     subtotal_produk: int,
     *     shipping_total: int,
     *     grand_total: int,
     *     has_blocked_store: bool,
     *     payment_method: PaymentMethod,
     *     errors: array<int, string>,
     * }
     */
    public function build(
        Cart $cart,
        User $buyer,
        ?array $buyerAddressOverride = null,
        ?PaymentMethod $paymentMethod = null,
    ): array
    {
        $paymentMethod ??= PaymentMethod::Online;
        $isPickup       = $paymentMethod->isPickup();

        $buyerAddress = $buyerAddressOverride ?? $buyer->addressSnapshot();
        $errors       = [];

        $hasBuyerAddress = ! empty($buyerAddress['city_id']) && ! empty($buyerAddress['district_id']);

        // Validasi alamat pembeli — opsional untuk Pickup karena pelanggan
        // datang ke toko, alamat hanya dipakai untuk identifikasi penerima.
        if (! $hasBuyerAddress && ! $isPickup) {
            $errors[] = 'Lengkapi alamat pengiriman (provinsi, kota, kecamatan) di profil terlebih dahulu.';
        }

        // Kelompokkan item berdasarkan store_id (= product->user_id).
        $groups = [];
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product) {
                $errors[] = 'Beberapa produk di keranjang sudah tidak tersedia.';
                continue;
            }

            $weight = (float) $product->weight_kg;
            if ($weight <= 0) {
                $errors[] = 'Produk "'.$product->nama.'" belum memiliki berat yang valid.';
            }

            $storeId = $product->user_id;
            if (! isset($groups[$storeId])) {
                $groups[$storeId] = [
                    'store'           => $product->petani,
                    'items'           => [],
                    'subtotal'        => 0,
                    'total_weight_kg' => 0.0,
                ];
            }

            $lineSubtotal = (float) $product->harga * $item->jumlah;
            $lineWeight   = $weight * $item->jumlah;

            $groups[$storeId]['items'][]          = $item;
            $groups[$storeId]['subtotal']        += $lineSubtotal;
            $groups[$storeId]['total_weight_kg'] += $lineWeight;
        }

        $stores          = [];
        $subtotalProduk  = 0;
        $shippingTotal   = 0;
        $hasBlockedStore = false;

        foreach ($groups as $storeId => $group) {
            $store           = $group['store'];
            $weightCeil      = (int) ceil($group['total_weight_kg']);
            $storeHasAddress = $store && $store->hasCompleteAddress();

            // Pickup: ongkir selalu 0, tidak peduli alamat toko/pembeli.
            // Pembeli yang akan datang ke toko, jadi alamat tidak relevan untuk ongkir.
            if ($isPickup) {
                $shippingInfo = [
                    'available'        => true,
                    'zone'             => 'pickup',
                    'zone_label'       => 'Ambil di Toko',
                    'base_fee'         => 0,
                    'base_weight_kg'   => 0,
                    'extra_fee_per_kg' => 0,
                    'total_weight_kg'  => $weightCeil,
                    'shipping_cost'    => 0,
                    'message'          => $storeHasAddress
                        ? 'Ambil sendiri di toko: '.$store->district_name.', '.$store->city_name.'. Bayar tunai saat ambil.'
                        : 'Ambil sendiri di toko. Hubungi penjual untuk titik temu.',
                ];

                $stores[] = [
                    'store_id'        => $storeId,
                    'store'           => $store,
                    'items'           => $group['items'],
                    'subtotal'        => (int) $group['subtotal'],
                    'total_weight_kg' => $weightCeil,
                    'shipping'        => $shippingInfo,
                ];
                $subtotalProduk += (int) $group['subtotal'];
                continue;
            }

            if (! $storeHasAddress) {
                // Toko tanpa alamat lengkap diblok agar tidak ambigu untuk simulasi.
                $hasBlockedStore = true;
                $stores[] = [
                    'store_id'        => $storeId,
                    'store'           => $store,
                    'items'           => $group['items'],
                    'subtotal'        => (int) $group['subtotal'],
                    'total_weight_kg' => $weightCeil,
                    'shipping'        => [
                        'available'        => false,
                        'zone'             => null,
                        'zone_label'       => 'Alamat Toko Tidak Lengkap',
                        'base_fee'         => 0,
                        'base_weight_kg'   => 5,
                        'extra_fee_per_kg' => 0,
                        'total_weight_kg'  => $weightCeil,
                        'shipping_cost'    => 0,
                        'message'          => 'Toko ini belum melengkapi alamatnya. Tidak dapat memproses pengiriman.',
                    ],
                ];
                $subtotalProduk += (int) $group['subtotal'];
                continue;
            }

            // Hanya panggil ShippingService bila pembeli sudah punya alamat lengkap.
            if ($hasBuyerAddress && $group['total_weight_kg'] > 0) {
                $shippingInfo = $this->shipping->calculateShipping(
                    $store->addressSnapshot(),
                    $buyerAddress,
                    $group['total_weight_kg'],
                );
            } else {
                $shippingInfo = [
                    'available'        => false,
                    'zone'             => null,
                    'zone_label'       => 'Belum dapat dihitung',
                    'base_fee'         => 0,
                    'base_weight_kg'   => 5,
                    'extra_fee_per_kg' => 0,
                    'total_weight_kg'  => $weightCeil,
                    'shipping_cost'    => 0,
                    'message'          => 'Lengkapi alamat & berat produk terlebih dahulu.',
                ];
            }

            if (! $shippingInfo['available']) {
                $hasBlockedStore = true;
            }

            $stores[] = [
                'store_id'        => $storeId,
                'store'           => $store,
                'items'           => $group['items'],
                'subtotal'        => (int) $group['subtotal'],
                'total_weight_kg' => $shippingInfo['total_weight_kg'],
                'shipping'        => $shippingInfo,
            ];

            $subtotalProduk += (int) $group['subtotal'];
            $shippingTotal  += (int) $shippingInfo['shipping_cost'];
        }

        if ($hasBlockedStore && ! $isPickup) {
            $errors[] = 'Ada toko yang berada di luar kota/kabupaten Anda atau alamat belum lengkap. Hapus dulu produknya dari keranjang, atau pilih opsi "Ambil di Toko".';
        }

        $grandTotal = $subtotalProduk + $shippingTotal;

        return [
            'stores'            => $stores,
            'subtotal_produk'   => $subtotalProduk,
            'shipping_total'    => $shippingTotal,
            'grand_total'       => $grandTotal,
            'has_blocked_store' => $hasBlockedStore,
            'payment_method'    => $paymentMethod,
            'errors'            => array_values(array_unique($errors)),
        ];
    }
}
