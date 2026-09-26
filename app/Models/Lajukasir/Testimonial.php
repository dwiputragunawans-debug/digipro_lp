<?php

namespace App\Models\Lajukasir;

use App\Models\Concerns\HasUploadedImage;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu testimoni pemilik toko pada bagian "Kata Pemilik Toko".
 *
 * Trait gambar memakai kolom "photo", jadi namanya disesuaikan supaya
 * pemanggilnya di Blade tetap terbaca jelas.
 */
class Testimonial extends Model
{
    use HasUploadedImage;

    protected $table = 'lajukasir_testimonials';

    /**
     * @var list<string>
     */
    protected $guarded = [];

    protected function imageColumn(): string
    {
        return 'photo';
    }

    public function hasPhoto(): bool
    {
        return $this->hasImage();
    }

    public function photoUrl(): ?string
    {
        return $this->imageUrl();
    }
}
