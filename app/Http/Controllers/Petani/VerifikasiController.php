<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.petani.verifikasi.index', [
            'petani' => $request->user(),
            'status' => $request->user()->verificationStatus(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->is_verified) {
            return back()->with('error', 'Akun Anda sudah terverifikasi.');
        }

        $data = $request->validate([
            'nama_usaha'      => ['required', 'string', 'max:255'],
            'deskripsi_usaha' => ['required', 'string', 'max:2000'],
            'no_hp'           => ['required', 'string', 'max:20'],
            'alamat'          => ['required', 'string', 'max:500'],
            'nik'             => ['required', 'digits:16'],
            'ktp_image'       => [$user->ktp_image ? 'nullable' : 'required', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('ktp_image')) {
            if ($user->ktp_image && ! str_starts_with($user->ktp_image, 'http')) {
                Storage::disk('public')->delete($user->ktp_image);
            }
            $data['ktp_image'] = $request->file('ktp_image')->store('ktp', 'public');
        } else {
            unset($data['ktp_image']);
        }

        $data['verification_submitted_at'] = now();
        $data['verification_note']         = null;

        $user->update($data);

        return redirect()
            ->route('petani.verifikasi.index')
            ->with('success', 'Data verifikasi berhasil dikirim. Menunggu review admin.');
    }
}
