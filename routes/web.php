<?php

use App\Http\Controllers\LajukasirLandingController;
use App\Http\Middleware\TrackVisitor;
use App\Models\CtaSection;
use App\Models\Feature;
use App\Models\Hero;
use App\Models\Lajukasir\Setting as LajukasirSetting;
use App\Models\Portfolio;
use App\Models\Process;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Support\Analytics\VisitorRecorder;
use App\Support\Seo\Robots;
use App\Support\Seo\Sitemap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman LajuKasir dilayani dari subdomainnya sendiri, sedangkan halaman
// DigiPro Solution memakai domain utama. Daftar alamatnya ada di
// config/lajukasir.php: subdomain sebenarnya, ditambah alias .localhost supaya
// halamannya bisa dibuka di komputer sendiri tanpa menyunting /etc/hosts.
//
// Grup ini sengaja didaftarkan lebih dahulu: route tanpa batasan domain di bawah
// ikut mencocoki host apa pun, sehingga "/" milik DigiPro Solution akan menang
// di subdomain LajuKasir kalau urutannya dibalik. Ada pengujiannya di
// tests/Feature/LajukasirLandingTest.php supaya urutan ini tidak terbalik lagi.
foreach (array_values(config('lajukasir.hosts')) as $index => $host) {
    // Hanya alamat resmi yang memakai nama route tanpa akhiran, supaya
    // route('lajukasir.landing') tetap menunjuk ke subdomain sebenarnya.
    $suffix = $index === 0 ? '' : '.alias';

    Route::domain($host)->group(function () use ($suffix) {
        Route::get('/', LajukasirLandingController::class)
            ->middleware(TrackVisitor::class)
            ->name('lajukasir.landing'.$suffix);

        // sitemap.xml dan robots.txt halaman LajuKasir dibuat dari pengaturan di
        // menu Pengaturan LajuKasir, sehingga alamat yang dipakai selalu sama
        // dengan tag canonical di halamannya.
        Route::get('/sitemap.xml', fn () => response()
            ->view('seo.sitemap', [
                'urls' => Sitemap::urls(LajukasirSetting::current(), Sitemap::LAJUKASIR_CONTENT_MODELS),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
        )->name('lajukasir.seo.sitemap'.$suffix);

        Route::get('/robots.txt', fn () => response(Robots::content(LajukasirSetting::current()), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]))->name('lajukasir.seo.robots'.$suffix);
    });
}

// Pratinjau halaman LajuKasir di domain utama, supaya isinya bisa diperiksa
// tanpa menyetel subdomain di /etc/hosts. Hanyalah alat bantu pengembangan:
// halamannya diberi penanda noindex, kunjungannya sengaja tidak dicatat di
// analitik, dan route-nya bisa dimatikan lewat LAJUKASIR_PREVIEW=false.
if (config('lajukasir.preview_enabled')) {
    Route::get(config('lajukasir.preview_path'), LajukasirLandingController::class)
        ->name('lajukasir.preview')
        ->defaults('noindex', true);
}

Route::get('/', function () {
    $hero = Hero::first();
    $features = Feature::orderBy('order_column')->get();
    $services = Service::orderBy('order_column')->get();
    $portfolios = Portfolio::orderBy('order_column')->get();
    $processes = Process::orderBy('order_column')->get();
    $cta = CtaSection::first();
    $siteSetting = SiteSetting::current();

    return view('welcome', compact('hero', 'features', 'services', 'portfolios', 'processes', 'cta', 'siteSetting'));
})->middleware(TrackVisitor::class)->name('home');

// sitemap.xml dan robots.txt dibuat dari pengaturan SEO, sehingga alamat yang
// dipakai selalu sama dengan tag canonical di halaman.
Route::get('/sitemap.xml', fn () => response()
    ->view('seo.sitemap', ['urls' => Sitemap::urls()])
    ->header('Content-Type', 'application/xml; charset=UTF-8')
)->name('seo.sitemap');

Route::get('/robots.txt', fn () => response(Robots::content(), 200, [
    'Content-Type' => 'text/plain; charset=UTF-8',
]))->name('seo.robots');

// Klik tombol WhatsApp dilaporkan oleh JavaScript, lihat resources/js/app.js.
// Throttle-nya menjaga tabel analitik dari kiriman yang berlebihan.
Route::post('/analytics/whatsapp-click', function (Request $request) {
    $data = $request->validate([
        'path' => ['nullable', 'string', 'max:255'],
    ]);

    // Halaman yang dikirim JavaScript, dengan halaman pengirim sebagai cadangan.
    $path = $data['path'] ?? parse_url((string) $request->headers->get('referer'), PHP_URL_PATH) ?? '/';

    VisitorRecorder::recordWhatsAppClick($request, VisitorRecorder::visitorId($request), (string) $path);

    return response()->noContent();
})->middleware('throttle:60,1')->name('analytics.whatsapp-click');
