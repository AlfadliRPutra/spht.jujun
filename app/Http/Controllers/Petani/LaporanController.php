<?php

namespace App\Http\Controllers\Petani;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    private const SORT_MAP = [
        'latest'     => ['created_at', 'desc', 'Terbaru'],
        'oldest'     => ['created_at', 'asc',  'Terlama'],
        'total_desc' => ['total_harga','desc', 'Total Tertinggi'],
        'total_asc'  => ['total_harga','asc',  'Total Terendah'],
    ];

    public function index(Request $request): View
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = \array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = \in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $petaniId = $request->user()->id;

        $baseQuery = fn () => Order::query()
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $petaniId))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('id', $request->input('q'))
                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$request->input('q').'%'))));

        $items = (clone $baseQuery())
            ->with(['user', 'items.product'])
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        $statusCounts = [];
        foreach (OrderStatus::cases() as $s) {
            $statusCounts[$s->value] = (clone $baseQuery())->where('status', $s)->count();
        }

        $completedOrders = (clone $baseQuery())
            ->where('status', OrderStatus::Selesai)
            ->with('items.product')
            ->get();

        $totalPendapatan = 0;
        $totalTerjual = 0;
        foreach ($completedOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->product?->user_id === $petaniId) {
                    $totalPendapatan += (float) $item->harga * $item->jumlah;
                    $totalTerjual   += $item->jumlah;
                }
            }
        }

        return view('pages.petani.laporan.index', [
            'items'          => $items,
            'statuses'       => OrderStatus::cases(),
            'statusCounts'   => $statusCounts,
            'totalPendapatan'=> $totalPendapatan,
            'totalTerjual'   => $totalTerjual,
            'totalTransaksi' => array_sum($statusCounts),
            'petaniId'       => $petaniId,
            'sort'           => $sort,
            'sortOptions'    => $sortOptions,
            'perPage'        => $perPage,
        ]);
    }

    public function pdf(Request $request)
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = \array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $petaniId = $request->user()->id;

        $baseQuery = fn () => Order::query()
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $petaniId))
            ->when($request->filled('status'),    fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn ($q) => $q->whereDate('created_at', '<=', $request->input('date_to')));

        // Get all items without pagination for PDF
        $items = (clone $baseQuery())
            ->with(['user', 'items.product'])
            ->orderBy($sortCol, $sortDir)
            ->get();

        $statusCounts = [];
        foreach (OrderStatus::cases() as $s) {
            $statusCounts[$s->value] = (clone $baseQuery())->where('status', $s)->count();
        }

        $completedOrders = (clone $baseQuery())
            ->where('status', OrderStatus::Selesai)
            ->with('items.product')
            ->get();

        $totalPendapatan = 0;
        $totalTerjual = 0;
        foreach ($completedOrders as $order) {
            foreach ($order->items as $item) {
                if ($item->product?->user_id === $petaniId) {
                    $totalPendapatan += (float) $item->harga * $item->jumlah;
                    $totalTerjual   += $item->jumlah;
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.petani.laporan.pdf', [
            'items'          => $items,
            'totalPendapatan'=> $totalPendapatan,
            'totalTerjual'   => $totalTerjual,
            'totalTransaksi' => array_sum($statusCounts),
            'petani'         => $request->user(),
            'dateFrom'       => $request->input('date_from'),
            'dateTo'         => $request->input('date_to'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-petani.pdf');
    }
}
