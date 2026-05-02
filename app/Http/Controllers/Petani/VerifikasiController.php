<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Support\PublicUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function dismiss(Request $request): RedirectResponse
    {
        $request->session()->put('verifyModalDismissed', true);

        return back();
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
            'ktp_image'       => [
                $user->ktp_image ? 'nullable' : 'required',
                'image', 'mimes:jpg,jpeg,png,webp', 'max:4096',
            ],
        ], [
            'nama_usaha.required'      => 'Nama usaha wajib diisi.',
            'nama_usaha.max'           => 'Nama usaha maksimal :max karakter.',
            'deskripsi_usaha.required' => 'Deskripsi usaha wajib diisi.',
            'deskripsi_usaha.max'      => 'Deskripsi usaha maksimal :max karakter.',
            'no_hp.required'           => 'No. HP wajib diisi.',
            'no_hp.max'                => 'No. HP maksimal :max karakter.',
            'alamat.required'          => 'Alamat wajib diisi.',
            'alamat.max'               => 'Alamat maksimal :max karakter.',
            'nik.required'             => 'NIK wajib diisi.',
            'nik.digits'               => 'NIK harus 16 digit angka.',
            'ktp_image.required'       => 'Foto KTP wajib diunggah.',
            'ktp_image.image'          => 'File yang diunggah harus berupa gambar.',
            'ktp_image.mimes'          => 'Foto KTP harus berformat JPG, PNG, atau WEBP.',
            'ktp_image.max'            => 'Ukuran foto KTP maksimal 4 MB.',
        ]);

        if ($request->hasFile('ktp_image')) {
            PublicUpload::delete($user->ktp_image);
            $data['ktp_image'] = PublicUpload::store($request->file('ktp_image'), 'ktp');
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
