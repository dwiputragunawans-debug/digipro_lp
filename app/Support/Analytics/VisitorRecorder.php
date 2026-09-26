<?php

namespace App\Support\Analytics;

use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mencatat kunjungan halaman dan klik tombol WhatsApp.
 *
 * Tidak ada alamat IP yang disimpan. Pengunjung dikenali lewat id acak di cookie
 * yang diubah menjadi hash sebelum masuk database, cukup untuk menghitung
 * pengunjung unik per hari tanpa menyimpan data pribadi.
 *
 * Pengenalan perangkat, browser, sistem operasi dan asal pengunjung dilakukan
 * sederhana tanpa pustaka tambahan.
 */
class VisitorRecorder
{
    /**
     * Nama cookie penanda pengunjung. Isinya id acak, bukan data pribadi.
     */
    public const COOKIE = 'dgp_visitor';

    /**
     * Cookie bertahan satu tahun supaya pengunjung yang kembali masih dikenali
     * sebagai orang yang sama, bukan sebagai pengunjung baru.
     */
    public const COOKIE_LIFETIME = 60 * 24 * 365;

    /**
     * Ciri User-Agent yang jelas milik robot: mesin pencari, pratinjau tautan,
     * pemantau uptime dan skrip otomatis.
     *
     * @var list<string>
     */
    public const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit', 'headlesschrome',
        'lighthouse', 'pagespeed', 'pingdom', 'uptime', 'curl', 'wget',
        'python-requests', 'ahrefs', 'semrush', 'yandex', 'baidu',
    ];

    /**
     * Peta nama host referer ke asal pengunjung yang mudah dibaca.
     *
     * @var array<string, list<string>>
     */
    public const SOURCE_PATTERNS = [
        'Google' => ['google.', 'search.google'],
        'Bing' => ['bing.com'],
        'Yahoo' => ['yahoo.', 'search.yahoo'],
        'DuckDuckGo' => ['duckduckgo.com'],
        'Facebook' => ['facebook.com', 'fb.com', 'fb.me'],
        'Instagram' => ['instagram.com'],
        'WhatsApp' => ['wa.me', 'whatsapp.com', 'whatsapp.net'],
        'TikTok' => ['tiktok.com'],
        'YouTube' => ['youtube.com', 'youtu.be'],
        'LinkedIn' => ['linkedin.com', 'lnkd.in'],
        'X (Twitter)' => ['twitter.com', 't.co', 'x.com'],
        'Threads' => ['threads.net'],
    ];

    /**
     * Apakah kunjungan dari alamat ini masih boleh dicatat.
     *
     * Halamannya sendiri selalu dilayani; yang dibatasi hanyalah pencatatannya.
     * Satu alamat IP yang mengirim kunjungan bertubi-tubi — misalnya skrip yang
     * menyamar memakai User-Agent browser biasa — tidak bisa lagi membanjiri
     * tabel analytics_events dan menghabiskan kuota basis data hosting.
     *
     * Batasnya diatur lewat config/security.php
     * (ANALYTICS_MAX_VIEWS_PER_MINUTE).
     */
    public static function withinRecordingQuota(Request $request): bool
    {
        $limit = max(1, (int) config('security.max_recorded_views_per_minute', 60));

        try {
            $key = 'analytics-views:'.$request->ip();

            if (RateLimiter::tooManyAttempts($key, $limit)) {
                return false;
            }

            RateLimiter::hit($key, 60);

            return true;
        } catch (Throwable) {
            // Kunjungan tetap dicatat kalau penyimpanan cache sedang bermasalah.
            return true;
        }
    }

    /**
     * Id pengunjung dari cookie, atau id acak baru kalau cookie-nya belum ada.
     * Cookie-nya sendiri ditulis oleh App\Http\Middleware\TrackVisitor.
     */
    public static function visitorId(Request $request): string
    {
        $cookie = $request->cookie(self::COOKIE);

        return is_string($cookie) && $cookie !== '' ? $cookie : (string) Str::uuid();
    }

    /**
     * Apakah permintaan ini pantas dicatat: permintaan halaman biasa dari
     * manusia, bukan dari robot dan bukan dari pengunjung yang mematikan
     * pelacakan.
     */
    public static function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET') || $request->ajax() || $request->headers->has('X-Livewire')) {
            return false;
        }

        // Do Not Track dan Sec-GPC dihormati: pengunjung seperti ini tidak
        // dicatat dan juga tidak diberi cookie.
        if ($request->headers->get('DNT') === '1' || $request->headers->get('Sec-GPC') === '1') {
            return false;
        }

        return ! self::isBot((string) $request->userAgent());
    }

    /**
     * Kunjungan halaman. Dipanggil untuk setiap permintaan di landing page,
     * jadi kegagalannya tidak boleh sampai merusak halaman.
     */
    public static function recordPageView(Request $request, string $visitor): void
    {
        self::record($request, $visitor, AnalyticsEvent::TYPE_PAGE_VIEW);
    }

    /**
     * Klik tombol WhatsApp. Path-nya datang dari JavaScript (halaman yang
     * sedang dibuka), dengan halaman pengirim sebagai cadangan.
     */
    public static function recordWhatsAppClick(Request $request, string $visitor, ?string $path = null): void
    {
        self::record($request, $visitor, AnalyticsEvent::TYPE_WHATSAPP_CLICK, $path);
    }

    /**
     * Menulis satu baris ke tabel analytics_events.
     */
    protected static function record(Request $request, string $visitor, string $type, ?string $path = null): void
    {
        $userAgent = (string) $request->userAgent();

        // Robot tidak dicatat. Kunjungan halaman sudah disaring sebelum ini,
        // dan penjagaan ini juga menahan kiriman dari skrip ke endpoint klik
        // WhatsApp.
        if (self::isBot($userAgent)) {
            return;
        }

        try {
            AnalyticsEvent::create([
                'type' => $type,
                'path' => self::normalizePath($path ?? $request->path()),
                // Halaman mana yang menghasilkan kejadian ini. Diperlukan
                // karena halaman DigiPro Solution dan halaman LajuKasir
                // sama-sama memakai path "/", lihat AnalyticsEvent::scopeForSite().
                'host' => self::analyticsHost($request),
                'device' => self::device($userAgent),
                'browser' => self::browser($userAgent),
                'platform' => self::platform($userAgent),
                'source' => self::source($request),
                // Hari menurut zona waktu dasbor, bukan menurut UTC.
                'visited_on' => AnalyticsEvent::today()->toDateString(),
                'visitor' => hash('sha256', $visitor),
            ]);
        } catch (Throwable) {
            // Halaman tetap tampil walau tabel analitiknya belum ada, misalnya
            // sebelum `php artisan migrate` dijalankan.
        }
    }

    /**
     * Path selalu ditulis dengan garis miring di depan dan tanpa garis miring
     * di belakang, supaya "/" dan "" dihitung sebagai halaman yang sama.
     */
    public static function normalizePath(string $path): string
    {
        return '/'.trim($path, '/');
    }

    /**
     * Nama host yang disimpan di kolom host.
     *
     * Alias pengembangan seperti lajukasir.localhost menyajikan halaman yang
     * sama dengan subdomain sebenarnya, jadi kunjungannya dicatat memakai
     * subdomain itu. Tanpa penyamaan ini kunjungan dari alias akan terhitung
     * sebagai milik DigiPro Solution, karena AnalyticsEvent::scopeForSite()
     * menghitung semua host selain subdomain LajuKasir sebagai situs utama.
     */
    public static function analyticsHost(Request $request): string
    {
        $host = strtolower($request->getHost());

        $aliases = array_map('strtolower', config('lajukasir.hosts', []));

        if (in_array($host, $aliases, true)) {
            return strtolower((string) config('lajukasir.host'));
        }

        return $host;
    }

    /**
     * User-Agent tanpa nama browser dianggap robot: robot dan skrip biasanya
     * mengirim User-Agent kosong atau berisi nama alatnya sendiri.
     */
    public static function isBot(string $userAgent): bool
    {
        $userAgent = strtolower(trim($userAgent));

        if ($userAgent === '') {
            return true;
        }

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($userAgent, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perangkat dari User-Agent. Deteksinya sengaja sederhana, tanpa pustaka
     * tambahan, karena yang dibutuhkan hanya ponsel, tablet atau desktop.
     *
     * Android hanya dilaporkan sebagai ponsel kalau User-Agentnya memuat
     * "Mobile": tablet Android tidak memuatnya, sedangkan ponsel memuatnya.
     */
    public static function device(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'ipad'),
            str_contains($userAgent, 'tablet'),
            str_contains($userAgent, 'android') && ! str_contains($userAgent, 'mobile') => AnalyticsEvent::DEVICE_TABLET,
            str_contains($userAgent, 'mobi'),
            str_contains($userAgent, 'iphone'),
            str_contains($userAgent, 'ipod') => AnalyticsEvent::DEVICE_MOBILE,
            default => AnalyticsEvent::DEVICE_DESKTOP,
        };
    }

    /**
     * Nama browser. Urutan pengecekannya penting: Edge dan Opera juga memuat
     * "Chrome" di User-Agentnya, dan Samsung Internet memuat keduanya.
     */
    public static function browser(string $userAgent): ?string
    {
        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'edg') => 'Edge',
            str_contains($userAgent, 'opr') || str_contains($userAgent, 'opera') => 'Opera',
            str_contains($userAgent, 'samsungbrowser') => 'Samsung Internet',
            str_contains($userAgent, 'chrome') || str_contains($userAgent, 'crios') => 'Chrome',
            str_contains($userAgent, 'firefox') || str_contains($userAgent, 'fxios') => 'Firefox',
            str_contains($userAgent, 'safari') => 'Safari',
            default => null,
        };
    }

    /**
     * Sistem operasi dari User-Agent, hanya untuk melengkapi keterangan
     * perangkat di dasbor.
     */
    public static function platform(string $userAgent): ?string
    {
        $userAgent = strtolower($userAgent);

        return match (true) {
            str_contains($userAgent, 'windows') => 'Windows',
            str_contains($userAgent, 'android') => 'Android',
            str_contains($userAgent, 'iphone'),
            str_contains($userAgent, 'ipad'),
            str_contains($userAgent, 'ipod') => 'iOS',
            str_contains($userAgent, 'mac os x') || str_contains($userAgent, 'macintosh') => 'macOS',
            str_contains($userAgent, 'linux') => 'Linux',
            default => null,
        };
    }

    /**
     * Asal pengunjung: parameter utm_source kalau ada, kalau tidak dilihat dari
     * header Referer.
     *
     * Host yang tidak ada di daftar tetap ditampilkan apa adanya, supaya admin
     * bisa melihat dari mana saja orang datang. Kunjungan dari halaman situs
     * sendiri dihitung sebagai kunjungan langsung.
     */
    public static function source(Request $request): string
    {
        $campaign = trim((string) $request->query('utm_source'));

        if ($campaign !== '') {
            return Str::limit(Str::title($campaign), 64, '');
        }

        $host = strtolower((string) parse_url((string) $request->headers->get('referer'), PHP_URL_HOST));

        if ($host === '' || $host === strtolower($request->getHost())) {
            return AnalyticsEvent::DIRECT_SOURCE;
        }

        foreach (self::SOURCE_PATTERNS as $source => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($host, $pattern)) {
                    return $source;
                }
            }
        }

        return Str::limit($host, 64, '');
    }
}
