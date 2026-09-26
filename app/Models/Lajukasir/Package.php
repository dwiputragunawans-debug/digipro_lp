<?php

namespace App\Models\Lajukasir;

use App\Support\TextLines;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kartu paket pada bagian "Paket Harga" halaman LajuKasir.
 *
 * Harga disimpan sebagai teks karena paket kasir lazim ditulis "Rp 1.500.000"
 * atau "Hubungi Kami", dan halaman ini tidak pernah menghitungnya sendiri.
 */
class Package extends Model
{
    protected $table = 'lajukasir_packages';

    /**
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_highlighted' => 'boolean',
        ];
    }

    /**
     * Keunggulan paket yang ditulis satu per baris di panel admin, siap diulang
     * di Blade.
     *
     * @return array<int, string>
     */
    protected function featureList(): Attribute
    {
        return Attribute::get(fn (): array => TextLines::split($this->features));
    }
}
