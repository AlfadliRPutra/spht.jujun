<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokoController extends Controller
{
    private const SORT_MAP = [
        'latest'        => ['created_at',     'desc', 'Terbaru'],
        'name_asc'      => ['name',           'asc',  'Nama A-Z'],
        'products_desc' => ['products_count', 'desc', 'Produk Terbanyak'],
        'products_asc'  => ['products_count', 'asc',  'Produk Tersedikit'],
    ];

    public function index(Request $request): View
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $items = User::query()
            ->where('role', UserRole::Petani)
            ->withCount([
                'products',
                'products as active_products_count' => fn ($q) => $q->where('is_active', true),
                'products as inactive_products_count' => fn ($q) => $q->where('is_active', false),
            ])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name',  'like', '%'.$request->input('q').'%')
                ->orWhere('nama_usaha', 'like', '%'.$request->input('q').'%')
                ->orWhere('email', 'like', '%'.$request->input('q').'%')))
            ->when($request->input('verified') === '1', fn ($q) => $q->where('is_verified', true))
            ->when($request->input('verified') === '0', fn ($q) => $q->where('is_verified', false))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admin.toko.index', compact('items', 'sort', 'sortOptions', 'perPage'));
    }

    public function show(User $petani): View
    {
        abort_unless($petani->role === UserRole::Petani, 404);

        $products = $petani->products()->with('category')->latest()->get();

        return view('pages.admin.toko.show', compact('petani', 'products'));
    }

    public function toggleProduct(Request $request, User $petani, Product $product): RedirectResponse
    {
        abort_unless($petani->role === UserRole::Petani, 404);
        abort_unless($product->user_id === $petani->id, 404);

        if ($product->is_active) {
            $data = $request->validate([
                'deactivation_reason' => ['required', 'string', 'max:500'],
            ]);
            $product->update([
                'is_active'           => false,
                'deactivation_reason' => $data['deactivation_reason'],
            ]);
            $msg = 'Produk "'.$product->nama.'" dinonaktifkan.';
        } else {
            $product->update([
                'is_active'           => true,
                'deactivation_reason' => null,
            ]);
            $msg = 'Produk "'.$product->nama.'" diaktifkan kembali.';
        }

        return back()->with('success', $msg);
    }
}
