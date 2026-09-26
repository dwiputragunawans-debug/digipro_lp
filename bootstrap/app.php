<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Header keamanan (X-Frame-Options, X-Content-Type-Options, dan
        // kawan-kawan) dipasang untuk setiap jawaban, termasuk halaman panel
        // admin dan berkas yang dilayani Laravel.
        $middleware->append(SecurityHeaders::class);

        // Alamat proxy yang boleh dipercaya, dipisah koma lewat TRUSTED_PROXIES:
        // perlu diisi kalau hostingnya memakai proxy atau Cloudflare, supaya
        // alamat pengunjung dan deteksi https ikut header X-Forwarded-*.
        //
        // Selama daftarnya kosong, header X-Forwarded-* dari luar diabaikan
        // sehingga alamat pengunjung tidak bisa dipalsukan — dan alamat
        // pengunjung itulah yang dipakai pembatas login dan pembatas pencatatan
        // analitik.
        $proxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', '')),
        )));

        if ($proxies !== []) {
            $middleware->trustProxies(at: $proxies);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
