<?php

namespace App\Http\Middleware;

use App\Support\Analytics\VisitorRecorder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat kunjungan halaman landing page dan menulis cookie penanda
 * pengunjung.
 *
 * Cookie-nya berisi id acak dan hanya dipakai untuk menghitung pengunjung unik
 * per hari; tidak ada data pribadi yang disimpan. Pengunjung yang mengirim
 * header DNT atau Sec-GPC tidak dicatat dan tidak diberi cookie sama sekali.
 *
 * Jumlah kunjungan yang dicatat dari satu alamat IP juga dibatasi
 * (VisitorRecorder::withinRecordingQuota()), supaya tabel analitik tidak bisa
 * dibanjiri kiriman bertubi-tubi. Halamannya tetap dilayani seperti biasa.
 */
class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (VisitorRecorder::shouldTrack($request) && VisitorRecorder::withinRecordingQuota($request)) {
            $visitor = VisitorRecorder::visitorId($request);

            VisitorRecorder::recordPageView($request, $visitor);

            if (blank($request->cookie(VisitorRecorder::COOKIE))) {
                // Cookie antre saja: dengan begitu ia ikut terkirim baik untuk
                // halaman biasa maupun untuk pengalihan.
                Cookie::queue(VisitorRecorder::COOKIE, $visitor, VisitorRecorder::COOKIE_LIFETIME);
            }
        }

        return $next($request);
    }
}
