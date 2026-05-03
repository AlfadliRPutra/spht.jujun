<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:8'],
            'role'           => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'no_hp'          => ['nullable', 'string', 'max:20'],
            'alamat'         => ['nullable', 'string', 'max:500'],
            'is_verified'    => ['sometimes', 'boolean'],
            'email_verified' => ['sometimes', 'boolean'],
        ]);

        $data['password']    = Hash::make($data['password']);
        $data['is_verified'] = $request->boolean('is_verified');

        // email_verified bukan kolom — disimpan via email_verified_at (bukan
        // mass-assignable). Set di memori dulu, lalu pakai forceFill+save.
        unset($data['email_verified']);

        $user = new User($data);
        $user->email_verified_at = $request->boolean('email_verified') ? now() : null;
        $user->save();

        return back()->with('success', 'Pengguna "'.$data['name'].'" ditambahkan.');
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'password'       => ['nullable', 'string', 'min:8'],
            'role'           => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
            'no_hp'          => ['nullable', 'string', 'max:20'],
            'alamat'         => ['nullable', 'string', 'max:500'],
            'is_verified'    => ['sometimes', 'boolean'],
            'email_verified' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_verified'] = $request->boolean('is_verified');

        $emailVerified = $request->boolean('email_verified');
        unset($data['email_verified']);

        $pengguna->fill($data);

        // email_verified_at ditangani di luar fillable. Toggle dengan eksplisit
        // supaya admin bisa baik me-verifikasi (skip /verify-email) maupun
        // me-batalkan verifikasi (forces /verify-email lagi saat user login).
        if ($emailVerified) {
            if ($pengguna->email_verified_at === null) {
                $pengguna->email_verified_at = now();
            }
        } else {
            $pengguna->email_verified_at = null;
        }

        $pengguna->save();

        return back()->with('success', 'Pengguna "'.$pengguna->name.'" diperbarui.');
    }

    public function destroy(Request $request, User $pengguna): RedirectResponse
    {
        if ($pengguna->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $nama = $pengguna->name;
        $pengguna->delete();

        return back()->with('success', 'Pengguna "'.$nama.'" dihapus.');
    }
}
