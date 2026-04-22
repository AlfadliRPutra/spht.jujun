<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $items = $request->user()->orders()
            ->when($request->filled('q'),      fn ($q) => $q->where('id', $request->input('q')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
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
}
