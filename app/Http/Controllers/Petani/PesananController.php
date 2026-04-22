<?php

namespace App\Http\Controllers\Petani;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
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
}
