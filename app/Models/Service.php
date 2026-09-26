<?php

namespace App\Models;

use App\Models\Concerns\HasIconName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kartu pada bagian "Solusi Digital untuk UMKM" di halaman depan.
 */
class Service extends Model
{
    use HasIconName;

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
     * Keunggulan yang ditulis satu per baris di panel admin, siap diulang di Blade.
     *
     * Baris kosong dibuang, dan penanda daftar di awal baris ("-", "*", atau "•")
     * ikut dihilangkan supaya admin bisa menulis dengan gaya apa pun.
     *
     * @return array<int, string>
     */
    protected function benefitList(): Attribute
    {
        return Attribute::get(function (): array {
            $lines = preg_split('/\r\n|\r|\n/', (string) $this->benefits) ?: [];

            return collect($lines)
                ->map(fn (string $line): string => trim(preg_replace('/^[-*•\s]+/u', '', trim($line)) ?? ''))
                ->filter()
                ->values()
                ->all();
        });
    }
}
