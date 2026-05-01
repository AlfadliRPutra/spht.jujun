<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PublicUpload
{
    public const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Simpan file ke public/uploads/{folder} dan kembalikan path relatif
     * (mis. "uploads/products/foo.webp") yang siap dipakai sebagai URL aset.
     */
    public static function store(UploadedFile $file, string $folder): string
    {
        $folder = trim($folder, '/');
        $dir    = public_path('uploads/'.$folder);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            $ext = 'jpg';
        }

        $name = Str::random(32).'.'.$ext;
        $file->move($dir, $name);

        return 'uploads/'.$folder.'/'.$name;
    }

    /**
     * Hapus file dari public/ jika ada. Aman dipanggil dengan path lama
     * dari storage symlink (mis. "products/foo.png") — diabaikan saja.
     */
    public static function delete(?string $relativePath): void
    {
        if (! $relativePath || ! str_starts_with($relativePath, 'uploads/')) {
            return;
        }

        $abs = public_path($relativePath);
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
