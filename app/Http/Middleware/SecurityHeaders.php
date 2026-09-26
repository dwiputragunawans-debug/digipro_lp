<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk setiap jawaban yang dibuat aplikasi.
 *
 * Header ini diminta browser untuk mematikan kebiasaan yang bisa disalahgunakan:
 * menebak tipe berkas, memasang halaman di dalam <iframe> situs lain, mengirim
 * alamat lengkap halaman ke situs luar, atau meminta izin perangkat yang tidak
 * dipakai situs ini.
 *
 * Dipasang lewat bootstrap/app.php dan tidak mengganti header yang sudah diisi
 * oleh aplikasi atau kerangka kerja.
 */
class SecurityHeaders
{
    /**
     * @var array<string, string>
     */
    protected const HEADERS = [
        // Browser tidak boleh menebak-nebak tipe berkas: berkas HTML yang
        // dikirim sebagai teks biasa pun tidak dieksekusi sebagai halaman.
        'X-Content-Type-Options' => 'nosniff',

        // Halaman tidak boleh dipasang di dalam <iframe> situs lain, sehingga
        // tidak bisa dipakai untuk klik palsu (clickjacking).
        'X-Frame-Options' => 'SAMEORIGIN',

        // Alamat lengkap halaman hanya ikut terkirim ke alamat sesama https;
        // situs lain cukup menerima asal situsnya saja.
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Fitur perangkat yang tidak dipakai situs ini ditutup.
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',

        // Berkas policy XML milik situs ini tidak boleh dibaca situs lain.
        'X-Permitted-Cross-Domain-Policies' => 'none',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::HEADERS as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        // HSTS hanya dikirim kalau permintaannya memang sudah lewat https,
        // supaya browser tidak dipaksa memakai https di server yang belum
        // memasang sertifikat.
        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }
}
