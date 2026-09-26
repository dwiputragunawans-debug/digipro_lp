<?php

namespace App\Models\Lajukasir;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu tanya jawab pada bagian "Pertanyaan Umum".
 */
class Faq extends Model
{
    protected $table = 'lajukasir_faqs';

    /**
     * @var list<string>
     */
    protected $guarded = [];
}
