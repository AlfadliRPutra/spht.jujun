<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    private const SORT_MAP = [
        'latest'       => ['created_at', 'desc', 'Terbaru'],
        'oldest'       => ['created_at', 'asc',  'Terlama'],
        'name_asc'     => ['name',       'asc',  'Nama A-Z'],
        'name_desc'    => ['name',       'desc', 'Nama Z-A'],
        'email_asc'    => ['email',      'asc',  'Email A-Z'],
    ];

    public function index(Request $request): View
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page')
            : 10;

        $items = User::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name',  'like', '%'.$request->input('q').'%')
                ->orWhere('email', 'like', '%'.$request->input('q').'%')))
            ->when($request->filled('role'),     fn ($q) => $q->where('role', $request->input('role')))
            ->when($request->input('verified') === '1', fn ($q) => $q->where('is_verified', true))
            ->when($request->input('verified') === '0', fn ($q) => $q->where('is_verified', false))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admin.pengguna.index', [
            'items'       => $items,
            'roles'       => UserRole::cases(),
            'sort'        => $sort,
            'sortOptions' => $sortOptions,
            'perPage'     => $perPage,
        ]);
    }
}
