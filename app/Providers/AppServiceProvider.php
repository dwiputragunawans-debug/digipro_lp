<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The site is written in Indonesian, and Carbon does not follow the
        // application locale on its own. Without this, translated date columns in
        // the admin panel would fall back to English month names.
        Carbon::setLocale(config('app.locale'));

        $this->hardenProductionDefaults();
    }

    /**
     * Penjaga terakhir untuk pengaturan yang berbahaya kalau tertinggal di server.
     *
     * Keduanya hanya berlaku untuk lingkungan produksi, jadi pengembangan di
     * komputer sendiri tidak terpengaruh.
     */
    protected function hardenProductionDefaults(): void
    {
        // APP_DEBUG yang masih "true" di produksi menampilkan jejak kode, isi
        // berkas .env dan kueri basis data kepada siapa pun yang memicu galat.
        // Nilainya dimatikan paksa di sini supaya satu baris .env yang lupa
        // diubah tidak membocorkan seluruh isi aplikasi.
        if ($this->app->environment('production')) {
            config(['app.debug' => false]);
        }

        // FORCE_HTTPS=true membuat semua alamat yang dibuat Laravel memakai
        // https, lihat config/security.php.
        if (config('security.force_https')) {
            URL::forceHttps();
        }
    }
}
