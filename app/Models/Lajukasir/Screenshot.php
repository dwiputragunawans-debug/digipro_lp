<?php

namespace App\Models\Lajukasir;

use App\Models\Concerns\HasUploadedImage;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu tangkapan layar aplikasi pada bagian "Tampilan Aplikasi".
 */
class Screenshot extends Model
{
    use HasUploadedImage;

    protected $table = 'lajukasir_screenshots';

    /**
     * @var list<string>
     */
    protected $guarded = [];
}
