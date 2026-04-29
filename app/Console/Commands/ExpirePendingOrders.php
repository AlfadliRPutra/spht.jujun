<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

/**
 * Bulk-cancel order Pending yang sudah lewat expires_at.
 *
 * Dijadwalkan tiap menit (lihat routes/console.php) — bersama lazy-expire di
 * PembayaranController, ini menjamin batas 10 menit ditegakkan walaupun user
 * tidak pernah membuka kembali halaman pembayaran.
 */
class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending';

    protected $description = 'Batalkan otomatis order Pending yang sudah melewati expires_at.';

    public function handle(): int
    {
        $count = Order::query()
            ->where('status', OrderStatus::Pending)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => OrderStatus::Batal]);

        if ($count > 0) {
            $this->info("Membatalkan $count order yang melewati batas waktu pembayaran.");
        }

        return self::SUCCESS;
    }
}
