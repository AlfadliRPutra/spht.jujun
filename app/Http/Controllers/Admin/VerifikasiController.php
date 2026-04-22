<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    private const SORT_MAP = [
        'latest'    => ['created_at', 'desc', 'Terbaru'],
        'oldest'    => ['created_at', 'asc',  'Terlama'],
        'name_asc'  => ['name',       'asc',  'Nama A-Z'],
        'name_desc' => ['name',       'desc', 'Nama Z-A'],
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
            ->where('is_verified', false)
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name',  'like', '%'.$request->input('q').'%')
                ->orWhere('email', 'like', '%'.$request->input('q').'%')))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admin.verifikasi.index', compact('items', 'sort', 'sortOptions', 'perPage'));
    }
}
