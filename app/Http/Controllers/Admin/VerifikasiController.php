<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    private const SORT_MAP = [
        'latest'    => ['verification_submitted_at', 'desc', 'Terbaru'],
        'oldest'    => ['verification_submitted_at', 'asc',  'Terlama'],
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

        $statusFilter = $request->input('status', 'pending');

        $items = User::query()
            ->where('role', UserRole::Petani)
            ->when($statusFilter === 'pending', fn ($q) => $q->where('is_verified', false)->whereNotNull('verification_submitted_at'))
            ->when($statusFilter === 'verified', fn ($q) => $q->where('is_verified', true))
            ->when($statusFilter === 'rejected', fn ($q) => $q->where('is_verified', false)->whereNull('verification_submitted_at')->whereNotNull('verification_note'))
            ->when($statusFilter === 'not_submitted', fn ($q) => $q->where('is_verified', false)->whereNull('verification_submitted_at')->whereNull('verification_note'))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name',  'like', '%'.$request->input('q').'%')
                ->orWhere('email', 'like', '%'.$request->input('q').'%')
                ->orWhere('nama_usaha', 'like', '%'.$request->input('q').'%')))
            ->orderBy($sortCol, $sortDir)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('pages.admin.verifikasi.index', [
            'items'        => $items,
            'sort'         => $sort,
            'sortOptions'  => $sortOptions,
            'perPage'      => $perPage,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function show(User $petani): View
    {
        abort_unless($petani->role === UserRole::Petani, 404);

        return view('pages.admin.verifikasi.show', compact('petani'));
    }

    public function approve(User $petani): RedirectResponse
    {
        abort_unless($petani->role === UserRole::Petani, 404);

        $petani->update([
            'is_verified'       => true,
            'verification_note' => null,
        ]);

        return redirect()
            ->route('admin.verifikasi.index')
            ->with('success', 'Petani '.$petani->name.' berhasil diverifikasi.');
    }

    public function reject(Request $request, User $petani): RedirectResponse
    {
        abort_unless($petani->role === UserRole::Petani, 404);

        $data = $request->validate([
            'verification_note' => ['required', 'string', 'max:500'],
        ]);

        $petani->update([
            'is_verified'               => false,
            'verification_submitted_at' => null,
            'verification_note'         => $data['verification_note'],
        ]);

        return redirect()
            ->route('admin.verifikasi.index')
            ->with('success', 'Pengajuan '.$petani->name.' ditolak.');
    }
}
