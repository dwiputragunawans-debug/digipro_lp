<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Satu kartu pada bagian "Karya Kami" di halaman depan.
 */
class Portfolio extends Model
{
    protected $guarded = [];

    /**
     * Whether an image has been uploaded and is still present on the public disk.
     */
    public function hasImage(): bool
    {
        return filled($this->image) && static::imageFileExists($this->image);
    }

    /**
     * Root relative URL of the uploaded image, so that it is always loaded from
     * the same origin as the page that renders it.
     */
    public function imageUrl(): ?string
    {
        return $this->hasImage() ? Storage::disk('public')->url($this->image) : null;
    }

    protected static function imageFileExists(?string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (Throwable) {
            return false;
        }
    }
}
