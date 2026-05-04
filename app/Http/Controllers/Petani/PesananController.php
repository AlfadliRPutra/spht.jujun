<?php

namespace App\Http\Controllers\Petani;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesananController extends Controller
{
    private const SORT_MAP = [
        'latest'      => ['created_at', 'desc', 'Terbaru'],
        'oldest'      => ['created_at', 'asc',  'Terlama'],
        'total_desc'  => ['total_harga','desc', 'Total Tertinggi'],
        'total_asc'   => ['total_harga','asc',  'Total Terendah'],
    ];

    public function index(Request $request): View
    {
        // Cancel-on-view: order Pending yang sudah lewat batas pembayaran
        // langsung berstatus Batal di tampilan ini juga.
        Order::expireOverdue();

        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $petaniId = $request->user()->id;

        $items = Order::query()
            ->with(['user', 'items.product'])
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $petaniId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('id', $request->input('q'))
                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$request->input('q').'%'))))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.petani.pesanan.index', [
            'items'       => $items,
            'statuses'    => OrderStatus::cases(),
            'petaniId'    => $petaniId,
            'sort'        => $sort,
            'sortOptions' => $sortOptions,
            'perPage'     => $perPage,
        ]);
    }

    public function ship(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order, $request->user()->id);

        if ($order->status !== OrderStatus::Dibayar) {
            return back()->with('error', 'Pesanan hanya bisa dikirim setelah dibayar.');
        }

        $order->update(['status' => OrderStatus::Dikirim]);

        return back()->with('success', 'Pesanan #'.$order->id.' ditandai sudah dikirim.');
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order, $request->user()->id);

        if ($order->status !== OrderStatus::Dikirim) {
            return back()->with('error', 'Pesanan hanya bisa diselesaikan setelah dikirim.');
        }

        $petaniId = $request->user()->id;

        $order->loadMissing('items.product');
        foreach ($order->items as $item) {
            if ($item->product?->user_id === $petaniId) {
                $item->product->increment('sold_count', $item->jumlah);
            }
        }

        $order->update(['status' => OrderStatus::Selesai]);

        return back()->with('success', 'Pesanan #'.$order->id.' diselesaikan.');
    }

    /**
     * Tampilkan resi pengiriman printable. Hanya untuk pesanan milik petani
     * yang sudah dibayar (Dibayar/Dikirim/Selesai). Layout sengaja minimal +
     * @media print supaya rapi saat dicetak ke kertas A5/struk.
     */
    public function resi(Request $request, Order $order): View
    {
        $petaniId = $request->user()->id;
        $this->authorizeOrder($order, $petaniId);

        if (in_array($order->status, [OrderStatus::Pending, OrderStatus::Batal], true)) {
            abort(404);
        }

        $order->load(['user', 'items.product', 'shippings']);

        $petani   = $request->user();
        $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
        $shipping = $order->shippings->firstWhere('store_id', $petaniId);

        return view('pages.petani.pesanan.resi', [
            'order'    => $order,
            'petani'   => $petani,
            'ownItems' => $ownItems,
            'shipping' => $shipping,
        ]);
    }

    public function invoice(Request $request, Order $order)
    {
        $petaniId = $request->user()->id;
        $this->authorizeOrder($order, $petaniId);

        if (in_array($order->status, [OrderStatus::Pending, OrderStatus::Batal], true)) {
            abort(404);
        }

        $order->load(['user', 'items.product', 'shippings']);

        $petani   = $request->user();
        $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
        $shipping = $order->shippings->firstWhere('store_id', $petaniId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'order'    => $order,
            'petani'   => $petani,
            'ownItems' => $ownItems,
            'shipping' => $shipping,
            'role'     => 'petani'
        ])->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm width roughly

        return $pdf->stream('invoice-pesanan-'.$order->id.'.pdf');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order, $request->user()->id);

        if (in_array($order->status, [OrderStatus::Selesai, OrderStatus::Batal], true)) {
            return back()->with('error', 'Pesanan ini sudah final, tidak bisa dibatalkan.');
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $order->update([
            'status' => OrderStatus::Batal,
        ]);

        return back()->with('success', 'Pesanan #'.$order->id.' dibatalkan. Alasan: '.$request->input('cancel_reason'));
    }

    private function authorizeOrder(Order $order, int $petaniId): void
    {
        $hasOwn = $order->items()
            ->whereHas('product', fn ($q) => $q->where('user_id', $petaniId))
            ->exists();

        abort_unless($hasOwn, 403);
    }
}
