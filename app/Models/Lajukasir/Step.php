<?php

namespace App\Models\Lajukasir;

use App\Support\Heroicons;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu langkah pada bagian "Cara Mulai" halaman LajuKasir.
 */
class Step extends Model
{
    protected $table = 'lajukasir_steps';

    /**
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Ikon di dalam lingkaran langkah, atau null kalau kolomnya kosong atau
     * isinya tidak dikenal — supaya yang tampil kembali nomor langkahnya.
     *
     * Berbeda dengan kartu Fitur, di sini ikon cadangan (petir) justru
     * membingungkan, jadi tidak dipakai.
     */
    protected function stepIcon(): Attribute
    {
        return Attribute::get(fn (): ?string => Heroicons::resolve($this->icon));
    }
}
