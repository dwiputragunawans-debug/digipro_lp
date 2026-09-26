<?php

namespace App\Http\Controllers;

use App\Models\Lajukasir\Faq;
use App\Models\Lajukasir\Feature;
use App\Models\Lajukasir\Package;
use App\Models\Lajukasir\Screenshot;
use App\Models\Lajukasir\Setting;
use App\Models\Lajukasir\Step;
use App\Models\Lajukasir\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Halaman LajuKasir di subdomainnya sendiri.
 *
 * Seluruh isinya berasal dari menu grup "LajuKasir" di panel admin, jadi
 * halamannya bisa diubah tanpa menyentuh kode. Judul setiap bagian punya nilai
 * bawaan di resources/views/lajukasir/landing.blade.php, sedangkan bagian yang
 * daftar isinya masih kosong tidak ditampilkan sama sekali.
 */
class LajukasirLandingController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('lajukasir.landing', [
            'setting' => Setting::current(),
            'features' => Feature::query()->orderBy('order_column')->get(),
            'packages' => Package::query()->orderBy('order_column')->get(),
            'steps' => Step::query()->orderBy('order_column')->get(),
            'screenshots' => Screenshot::query()->orderBy('order_column')->get(),
            'faqs' => Faq::query()->orderBy('order_column')->get(),
            'testimonials' => Testimonial::query()->orderBy('order_column')->get(),
            // Alamat pratinjau memakai default route ini supaya halaman kembar
            // di domain utama tidak ikut terindeks mesin pencari.
            'noindex' => (bool) $request->route('noindex'),
        ]);
    }
}
