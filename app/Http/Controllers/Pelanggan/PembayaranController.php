<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\CheckoutSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification as MidtransNotification;
use Midtrans\Snap;
use Midtrans\Transaction as MidtransTransaction;

class PembayaranController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production');
        MidtransConfig::$isSanitized  = (bool) config('services.midtrans.is_sanitized');
        MidtransConfig::$is3ds        = (bool) config('services.midtrans.is_3ds');

        if (! MidtransConfig::$isProduction && app()->environment(['local', 'development', 'testing'])) {
            MidtransConfig::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_HTTPHEADER     => [],
            ];
        }
    }

    public function store(Request $request, CheckoutSummaryService $summary): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $user = $request->user();
        $cart = $user->cart()->with('items.product.petani')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('pelanggan.keranjang.index')
                ->with('error', 'Keranjang kosong.');
        }

        // Pastikan alamat yang dipilih milik user yang sedang checkout.
        $address = $user->addresses()->whereKey($data['address_id'])->first();
        if (! $address) {
            return redirect()->route('pelanggan.checkout.index')
                ->with('error', 'Alamat pengiriman tidak valid.');
        }

        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                return redirect()->route('pelanggan.keranjang.index')
                    ->with('error', 'Beberapa produk di keranjang sudah tidak tersedia.');
            }
            if ($item->jumlah > $item->product->stok) {
                return redirect()->route('pelanggan.keranjang.index')
                    ->with('error', 'Stok '.$item->product->nama.' tidak mencukupi.');
            }
        }

        // Hitung ulang ongkir per toko di server — pakai snapshot alamat terpilih.
        $checkout = $summary->build($cart, $user, $address->snapshot());

        if ($checkout['has_blocked_store']) {
            return redirect()->route('pelanggan.checkout.index', ['address_id' => $address->id])
                ->with('error', 'Beberapa toko tidak dapat melayani pengiriman ke alamat Anda. Hapus produknya dari keranjang.');
        }

        $order = DB::transaction(function () use ($cart, $user, $address, $checkout) {
            $order = Order::create([
                'user_id'                => $user->id,
                'subtotal_produk'        => $checkout['subtotal_produk'],
                'shipping_total'         => $checkout['shipping_total'],
                'voucher_discount'       => $checkout['voucher_discount'],
                'total_harga'            => $checkout['grand_total'],
                'status'                 => OrderStatus::Pending,
                'metode_pembayaran'      => 'midtrans',
                'nama_penerima'          => $address->nama_penerima,
                'no_hp_penerima'         => $address->no_hp_penerima,
                'alamat_pengiriman'      => $address->alamat,
                'shipping_province_id'   => $address->province_id,
                'shipping_province_name' => $address->province_name,
                'shipping_city_id'       => $address->city_id,
                'shipping_city_name'     => $address->city_name,
                'shipping_district_id'   => $address->district_id,
                'shipping_district_name' => $address->district_name,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'store_id'   => $item->product->user_id,
                    'jumlah'     => $item->jumlah,
                    'weight_kg'  => $item->product->weight_kg,
                    'harga'      => $item->product->harga,
                ]);
            }

            // Catat rincian ongkir per toko sebagai snapshot (audit & tampilan invoice).
            foreach ($checkout['stores'] as $group) {
                $order->shippings()->create([
                    'store_id'         => $group['store_id'],
                    'store_name'       => $group['store']->nama_usaha ?? $group['store']->name,
                    'zone'             => $group['shipping']['zone'],
                    'zone_label'       => $group['shipping']['zone_label'],
                    'base_fee'         => $group['shipping']['base_fee'],
                    'extra_fee_per_kg' => $group['shipping']['extra_fee_per_kg'],
                    'base_weight_kg'   => $group['shipping']['base_weight_kg'],
                    'total_weight_kg'  => $group['shipping']['total_weight_kg'],
                    'shipping_cost'    => $group['shipping']['shipping_cost'],
                ]);
            }

            return $order;
        });

        try {
            $snapToken = $this->generateSnapToken($order);
        } catch (\Throwable $e) {
            Log::error('Midtrans snap token error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            $order->update(['status' => OrderStatus::Batal]);
            return redirect()->route('pelanggan.keranjang.index')
                ->with('error', 'Gagal menginisialisasi pembayaran: '.$e->getMessage());
        }

        return redirect()->route('pelanggan.pembayaran.show', $order);
    }

    public function sync(Order $order, Request $request): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->syncStatusFromMidtrans($order);

        if (in_array($order->status, [OrderStatus::Dibayar, OrderStatus::Dikirim, OrderStatus::Selesai], true)) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('success', 'Status pembayaran diperbarui: '.$order->status->customerLabel().'.');
        }

        if ($order->status === OrderStatus::Batal) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('error', 'Pembayaran dibatalkan/kadaluarsa.');
        }

        return redirect()->route('pelanggan.pesanan.index')
            ->with('error', 'Pembayaran belum terkonfirmasi oleh Midtrans.');
    }

    public function latest(Request $request): RedirectResponse
    {
        $order = $request->user()->orders()
            ->where('status', OrderStatus::Pending)
            ->latest('id')
            ->first();

        if (! $order) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('error', 'Tidak ada pesanan yang menunggu pembayaran.');
        }

        return redirect()->route('pelanggan.pembayaran.show', $order);
    }

    public function show(Order $order, Request $request): View|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === OrderStatus::Batal) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('error', 'Pesanan ini sudah dibatalkan.');
        }

        if (in_array($order->status, [OrderStatus::Dibayar, OrderStatus::Dikirim, OrderStatus::Selesai], true)) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('success', 'Pesanan sudah dibayar.');
        }

        if (! $order->snap_token) {
            try {
                $this->generateSnapToken($order);
                $order->refresh();
            } catch (\Throwable $e) {
                Log::error('Midtrans snap token refresh error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
                return redirect()->route('pelanggan.keranjang.index')
                    ->with('error', 'Gagal memuat pembayaran: '.$e->getMessage());
            }
        }

        $order->load('items.product');

        return view('pages.pelanggan.pembayaran.index', [
            'order'      => $order,
            'clientKey'  => config('services.midtrans.client_key'),
            'production' => (bool) config('services.midtrans.is_production'),
        ]);
    }

    public function finish(Order $order, Request $request): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->syncStatusFromMidtrans($order);

        if ($order->status === OrderStatus::Dibayar
            || $order->status === OrderStatus::Dikirim
            || $order->status === OrderStatus::Selesai) {
            return redirect()->route('pelanggan.pesanan.index')
                ->with('success', 'Pembayaran berhasil. Terima kasih!');
        }

        return redirect()->route('pelanggan.pesanan.index')
            ->with('success', 'Pembayaran diproses. Status akan diperbarui otomatis setelah konfirmasi Midtrans.');
    }

    public function unfinish(Order $order, Request $request): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->syncStatusFromMidtrans($order);

        return redirect()->route('pelanggan.pesanan.index')
            ->with('error', 'Pembayaran belum selesai. Anda bisa melanjutkan dari halaman pesanan.');
    }

    public function error(Order $order, Request $request): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->syncStatusFromMidtrans($order);

        return redirect()->route('pelanggan.pesanan.index')
            ->with('error', 'Terjadi kesalahan pada pembayaran.');
    }

    public function notification(Request $request)
    {
        try {
            $notif = new MidtransNotification();
        } catch (\Throwable $e) {
            Log::error('Midtrans notification parse error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'invalid'], 400);
        }

        $midtransOrderId = $notif->order_id ?? null;
        if (! $midtransOrderId) {
            return response()->json(['message' => 'missing order_id'], 400);
        }

        $order = Order::where('midtrans_order_id', $midtransOrderId)->first();
        if (! $order) {
            return response()->json(['message' => 'order not found'], 404);
        }

        $serverKey = config('services.midtrans.server_key');
        $expected  = hash('sha512', $midtransOrderId.($notif->status_code ?? '').($notif->gross_amount ?? '').$serverKey);
        if (! isset($notif->signature_key) || ! hash_equals($expected, $notif->signature_key)) {
            Log::warning('Midtrans signature mismatch', ['order_id' => $midtransOrderId]);
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $this->applyMidtransStatus(
            $order,
            $notif->transaction_status ?? null,
            $notif->fraud_status ?? null,
            $notif->payment_type ?? null,
        );

        return response()->json(['message' => 'ok']);
    }

    private function syncStatusFromMidtrans(Order $order): void
    {
        if (! $order->midtrans_order_id) {
            return;
        }

        try {
            $status = MidtransTransaction::status($order->midtrans_order_id);
        } catch (\Throwable $e) {
            Log::warning('Midtrans status query failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
            return;
        }

        $data = is_array($status) ? $status : (array) $status;

        $this->applyMidtransStatus(
            $order,
            $data['transaction_status'] ?? null,
            $data['fraud_status'] ?? null,
            $data['payment_type'] ?? null,
        );
    }

    private function applyMidtransStatus(Order $order, ?string $status, ?string $fraud, ?string $paymentType): void
    {
        if (! $status) {
            return;
        }

        $order->payment_type   = $paymentType ?? $order->payment_type;
        $order->payment_status = $status;

        if (in_array($status, ['capture', 'settlement'], true)) {
            if ($status === 'capture' && $fraud === 'challenge') {
                $order->status = OrderStatus::Pending;
            } else {
                $wasPaid = in_array($order->status, [
                    OrderStatus::Dibayar,
                    OrderStatus::Dikirim,
                    OrderStatus::Selesai,
                ], true);

                if (! $wasPaid) {
                    DB::transaction(function () use ($order) {
                        $order->load('items', 'user.cart.items');
                        foreach ($order->items as $item) {
                            $product = Product::withTrashed()->lockForUpdate()->find($item->product_id);
                            if ($product) {
                                $product->decrement('stok', $item->jumlah);
                            }
                        }
                        if ($order->user && $order->user->cart) {
                            $order->user->cart->items()->delete();
                        }
                    });
                }

                $order->status  = OrderStatus::Dibayar;
                $order->paid_at = $order->paid_at ?? now();
            }
        } elseif (in_array($status, ['cancel', 'deny', 'expire', 'failure'], true)) {
            $order->status = OrderStatus::Batal;
        } elseif ($status === 'pending') {
            $order->status = OrderStatus::Pending;
        }

        $order->save();
    }

    private function generateSnapToken(Order $order): string
    {
        $order->load('items.product', 'user');

        if (! $order->midtrans_order_id) {
            $order->midtrans_order_id = 'SPHT-'.$order->id.'-'.now()->format('YmdHis');
            $order->save();
        }

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'id'       => (string) $item->product_id,
                'price'    => (int) round((float) $item->harga),
                'quantity' => (int) $item->jumlah,
                'name'     => mb_strimwidth($item->product?->nama ?? 'Produk', 0, 50, ''),
            ];
        }

        // Tambahkan ongkir per toko sebagai item terpisah agar
        // sum(item_details.price) === gross_amount (syarat Midtrans).
        $order->loadMissing('shippings');
        foreach ($order->shippings as $ship) {
            $shipCost = (int) round((float) $ship->shipping_cost);
            if ($shipCost <= 0) {
                continue;
            }
            $items[] = [
                'id'       => 'SHIP-'.$ship->id,
                'price'    => $shipCost,
                'quantity' => 1,
                'name'     => mb_strimwidth('Ongkir '.($ship->store_name ?? 'Toko'), 0, 50, ''),
            ];
        }

        $voucher = (int) round((float) ($order->voucher_discount ?? 0));
        if ($voucher > 0) {
            $items[] = [
                'id'       => 'VOUCHER-'.$order->id,
                'price'    => -$voucher,
                'quantity' => 1,
                'name'     => 'Diskon Voucher',
            ];
        }

        $grossAmount = (int) round((float) $order->total_harga);

        $nameParts = preg_split('/\s+/', trim((string) ($order->nama_penerima ?? $order->user->name)), 2) ?: [''];
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->midtrans_order_id,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $firstName ?: 'Pelanggan',
                'last_name'  => $lastName,
                'email'      => $order->user->email,
                'phone'      => $order->no_hp_penerima ?? $order->user->no_hp,
                'shipping_address' => [
                    'first_name' => $firstName ?: 'Pelanggan',
                    'last_name'  => $lastName,
                    'phone'      => $order->no_hp_penerima ?? $order->user->no_hp,
                    'address'    => $order->alamat_pengiriman,
                ],
            ],
            'callbacks' => [
                'finish' => route('pelanggan.pembayaran.finish', $order),
            ],
        ];

        $snapToken = Snap::getSnapToken($payload);

        $order->snap_token = $snapToken;
        $order->save();

        return $snapToken;
    }
}
