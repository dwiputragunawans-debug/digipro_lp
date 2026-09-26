<?php

namespace App\Models\Concerns;

use App\Support\Heroicons;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Dipakai model yang punya kolom "icon" dan menampilkan ikonnya di halaman depan.
 *
 * Isi kolom "icon" bisa berupa "bolt", "heroicon-o-bolt", atau kosong, jadi
 * aksesor ini yang menormalkannya (lihat App\Support\Heroicons).
 */
trait HasIconName
{
    /**
     * Nama komponen ikon yang siap dipakai di Blade, misalnya heroicon-o-bolt.
     */
    protected function iconName(): Attribute
    {
        return Attribute::get(fn (): string => Heroicons::normalize($this->icon));
    }
}
