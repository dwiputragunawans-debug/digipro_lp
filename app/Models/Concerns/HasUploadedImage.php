<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Model yang punya satu kolom berisi berkas gambar di disk publik, misalnya
 * tangkapan layar aplikasi dan foto pemilik toko di halaman LajuKasir.
 *
 * Berkasnya diperiksa dulu, jadi gambar yang sudah dihapus dari disk tidak
 * menghasilkan tag <img> yang rusak.
 */
trait HasUploadedImage
{
    /**
     * Nama kolom tempat berkas gambarnya disimpan.
     */
    protected function imageColumn(): string
    {
        return 'image';
    }

    /**
     * Whether an image has been uploaded and is still present on the public disk.
     */
    public function hasImage(): bool
    {
        $path = $this->getAttribute($this->imageColumn());

        return filled($path) && static::uploadedFileExists($path);
    }

    /**
     * Root relative URL of the uploaded image, so that it is always loaded from
     * the same origin as the page that renders it.
     */
    public function imageUrl(): ?string
    {
        return $this->hasImage() ? Storage::disk('public')->url($this->getAttribute($this->imageColumn())) : null;
    }

    protected static function uploadedFileExists(?string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (Throwable) {
            return false;
        }
    }
}
