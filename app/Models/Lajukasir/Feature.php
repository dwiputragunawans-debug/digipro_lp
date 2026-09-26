<?php

namespace App\Models\Lajukasir;

use App\Models\Concerns\HasIconName;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kartu pada bagian "Fitur Unggulan" halaman LajuKasir.
 */
class Feature extends Model
{
    use HasIconName;

    protected $table = 'lajukasir_features';

    /**
     * @var list<string>
     */
    protected $guarded = [];
}
