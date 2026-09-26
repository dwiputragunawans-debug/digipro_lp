<?php

namespace App\Models;

use App\Support\Heroicons;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu langkah pada bagian "Proses Mudah Memiliki Website" di halaman depan.
 */
class Process extends Model
{
    protected $guarded = [];

    /**
     * Ikon di dalam lingkaran langkah, atau null kalau kolomnya kosong atau
     * isinya tidak dikenal — supaya yang tampil kembali nomor langkahnya.
     *
     * Berbeda dengan kartu Fitur dan Layanan, di sini ikon cadangan (petir) justru
     * membingungkan, jadi tidak dipakai.
     */
    protected function stepIcon(): Attribute
    {
        return Attribute::get(fn (): ?string => Heroicons::resolve($this->icon));
    }
}
