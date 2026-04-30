<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    private const SORT_MAP = [
        'latest'     => ['created_at', 'desc', 'Terbaru'],
        'oldest'     => ['created_at', 'asc',  'Terlama'],
        'total_desc' => ['total_harga','desc', 'Total Tertinggi'],
        'total_asc'  => ['total_harga','asc',  'Total Terendah'],
    ];

    public function index(Request $request): View
    {
        // Cancel-on-view: pastikan order Pending yang sudah lewat batas
        // tampil sebagai Batal, bukan menunggu scheduled command.
        Order::expireOverdue();

        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $items = $request->user()->orders()
            ->with('items.product')
            ->when($request->filled('q'),      fn ($q) => $q->where('id', $request->input('q')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            // Pesanan dibatalkan selalu didorong ke bawah, terlepas dari sort yang dipilih.
            // Selebihnya pakai sort user (default: terbaru).
            ->orderByRaw("CASE WHEN status = ? THEN 1 ELSE 0 END ASC", [OrderStatus::Batal->value])
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.pelanggan.pesanan.index', [
            'items'       => $items,
            'statuses'    => OrderStatus::cases(),
            'sort'        => $sort,
            'sortOptions' => $sortOptions,
            'perPage'     => $perPage,
        ]);
    }

    /**
     * Konfirmasi penerimaan pesanan oleh pelanggan: status Dikirim → Selesai.
     * Hanya pemilik pesanan dan hanya saat statusnya Dikirim.
     */
    public function confirmReceived(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status !== OrderStatus::Dikirim) {
            return back()->with('error', 'Konfirmasi hanya bisa dilakukan setelah pesanan dikirim.');
        }

        DB::transaction(function () use ($order) {
            $order->loadMissing('items.product');
            // Catat sold_count saat pelanggan benar-benar menerima — sumber data
            // untuk peringkat "Paling Laris" lebih akurat dibanding saat dibayar.
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('sold_count', $item->jumlah);
                }
            }
            $order->update(['status' => OrderStatus::Selesai]);
        });

        return back()->with('success', 'Terima kasih! Pesanan #'.$order->id.' telah ditandai diterima.');
    }
}
