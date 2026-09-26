<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Satu baris untuk setiap kejadian di landing page: halaman dibuka atau tombol
 * WhatsApp diklik.
 *
 * Tidak ada data pribadi yang disimpan. Pengunjung dikenali lewat hash dari id
 * acak di cookie (lihat App\Support\Analytics\VisitorRecorder), sehingga cukup
 * untuk menghitung pengunjung unik per hari tanpa menyimpan alamat IP.
 *
 * Agregasi yang dipakai dasbor juga ada di kelas ini, supaya widget Filament
 * tetap tipis dan query-nya bisa diuji sendiri.
 */
class AnalyticsEvent extends Model
{
    public const TYPE_PAGE_VIEW = 'page_view';

    public const TYPE_WHATSAPP_CLICK = 'whatsapp_click';

    /**
     * Penanda situs mana yang dihitung oleh sebuah angka di dasbor.
     *
     * Halaman DigiPro Solution dan halaman LajuKasir dilayani dari dua alamat
     * berbeda tetapi keduanya memakai path "/", jadi angka keduanya dipisahkan
     * lewat kolom host pada tabel analytics_events.
     */
    public const SITE_DIGIPRO = 'digipro';

    public const SITE_LAJUKASIR = 'lajukasir';

    public const DEVICE_DESKTOP = 'desktop';

    public const DEVICE_TABLET = 'tablet';

    public const DEVICE_MOBILE = 'mobile';

    /**
     * Kunjungan tanpa referensi, misalnya pengunjung yang mengetik alamatnya
     * langsung atau membuka tautan dari aplikasi chat.
     */
    public const DIRECT_SOURCE = 'Langsung';

    /**
     * Label bahasa Indonesia untuk kolom device di dasbor.
     *
     * @var array<string, string>
     */
    public const DEVICE_LABELS = [
        self::DEVICE_DESKTOP => 'Desktop',
        self::DEVICE_TABLET => 'Tablet',
        self::DEVICE_MOBILE => 'Ponsel',
    ];

    /**
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        // "last_seen" hanya ada pada hasil query halaman terpopuler, yang
        // mengambil MAX(created_at).
        'last_seen' => 'datetime',
    ];

    /**
     * Awal hari ini menurut zona waktu dasbor (lihat config/analytics.php),
     * bukan menurut UTC.
     */
    public static function today(): Carbon
    {
        return now(config('analytics.timezone'))->startOfDay();
    }

    /**
     * Berapa hari yang ditampilkan di dasbor.
     */
    public static function days(): int
    {
        return max(1, (int) config('analytics.days', 30));
    }

    /**
     * Batas awal rentang beberapa hari terakhir, dihitung dari tengah malam
     * hari ini. Semua widget memakai helper ini supaya "30 hari terakhir"
     * berarti hal yang sama di seluruh dasbor.
     */
    public static function rangeStart(int $days): Carbon
    {
        return static::today()->subDays(max(1, $days) - 1);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePageViews(Builder $query): Builder
    {
        return $query->ofType(self::TYPE_PAGE_VIEW);
    }

    public function scopeWhatsAppClicks(Builder $query): Builder
    {
        return $query->ofType(self::TYPE_WHATSAPP_CLICK);
    }

    /**
     * Rentang tanggal kunjungan. Kolom visited_on dipakai supaya harinya
     * berganti di tengah malam waktu Indonesia, lihat config/analytics.php.
     */
    public function scopeBetween(Builder $query, Carbon $from, ?Carbon $until = null): Builder
    {
        return $query
            ->whereDate('visited_on', '>=', $from->toDateString())
            ->when($until, fn (Builder $query): Builder => $query->whereDate('visited_on', '<=', $until->toDateString()));
    }

    /**
     * Host subdomain halaman LajuKasir, lihat config/lajukasir.php.
     */
    public static function lajukasirHost(): string
    {
        return (string) config('lajukasir.host');
    }

    /**
     * Batasi query ke satu situs, lihat konstanta SITE_*.
     *
     * Halaman LajuKasir dihitung dari kejadian di subdomainnya sendiri. Situs
     * DigiPro Solution menghitung semua sisanya, termasuk baris lama dari masa
     * sebelum kolom host ada — waktu itu hanya DigiPro Solution yang online —
     * sehingga angka lama di dasbor tidak berubah setelah halaman LajuKasir
     * ditambahkan.
     */
    public function scopeForSite(Builder $query, ?string $site = null): Builder
    {
        $host = static::lajukasirHost();

        if ($site === self::SITE_LAJUKASIR) {
            return $query->where('host', $host);
        }

        return $query->where(function (Builder $query) use ($host): void {
            $query->where('host', '!=', $host)->orWhereNull('host');
        });
    }

    /**
     * Jumlah orang berbeda yang membuka minimal satu halaman.
     */
    public static function uniqueVisitors(Carbon $from, ?Carbon $until = null, ?string $site = null): int
    {
        return static::query()
            ->pageViews()
            ->between($from, $until)
            ->forSite($site)
            ->distinct()
            ->count('visitor');
    }

    /**
     * Jumlah halaman yang dibuka, termasuk kunjungan berulang orang yang sama.
     */
    public static function viewCount(Carbon $from, ?Carbon $until = null, ?string $site = null): int
    {
        return static::query()->pageViews()->between($from, $until)->forSite($site)->count();
    }

    /**
     * Jumlah klik tombol WhatsApp.
     */
    public static function whatsAppClickCount(Carbon $from, ?Carbon $until = null, ?string $site = null): int
    {
        return static::query()->whatsAppClicks()->between($from, $until)->forSite($site)->count();
    }

    /**
     * Jumlah orang berbeda yang mengklik tombol WhatsApp, sehingga satu orang
     * yang mengklik beberapa tombol tetap terhitung sekali.
     */
    public static function whatsAppClickVisitors(Carbon $from, ?Carbon $until = null, ?string $site = null): int
    {
        return static::query()
            ->whatsAppClicks()
            ->between($from, $until)
            ->forSite($site)
            ->distinct()
            ->count('visitor');
    }

    /**
     * Pengunjung unik, tampilan halaman dan klik WhatsApp per hari selama
     * beberapa hari terakhir.
     *
     * Hari tanpa data tetap muncul dengan angka nol, supaya garis grafiknya
     * tidak bolong dan jarak sumbu tanggalnya tetap sama.
     *
     * @return array{labels: list<string>, visitors: list<int>, views: list<int>, clicks: list<int>}
     */
    public static function perDay(?int $days = null, ?string $site = null): array
    {
        $days ??= static::days();

        $start = static::rangeStart($days);

        $visitors = static::dailyTotals(static::query()->pageViews()->forSite($site), $start, 'COUNT(DISTINCT visitor)');
        $views = static::dailyTotals(static::query()->pageViews()->forSite($site), $start, 'COUNT(*)');
        $clicks = static::dailyTotals(static::query()->whatsAppClicks()->forSite($site), $start, 'COUNT(*)');

        $labels = [];
        $visitorSeries = [];
        $viewSeries = [];
        $clickSeries = [];

        for ($day = 0; $day < $days; $day++) {
            $date = $start->copy()->addDays($day);
            $key = $date->toDateString();

            // Contoh label: "24 Sep".
            $labels[] = $date->translatedFormat('j M');
            $visitorSeries[] = (int) ($visitors[$key] ?? 0);
            $viewSeries[] = (int) ($views[$key] ?? 0);
            $clickSeries[] = (int) ($clicks[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'visitors' => $visitorSeries,
            'views' => $viewSeries,
            'clicks' => $clickSeries,
        ];
    }

    /**
     * Query halaman yang paling banyak dibuka, dikelompokkan per path.
     *
     * MAX(id) dipakai sebagai kunci baris: hasil GROUP BY tidak punya satu id,
     * sedangkan tabel di panel admin membutuhkannya untuk mengenali tiap baris.
     */
    public static function topPagesQuery(int $limit = 10, ?string $site = null): Builder
    {
        return static::query()
            ->pageViews()
            ->forSite($site)
            ->selectRaw('MAX(id) as id, path, COUNT(*) as views, COUNT(DISTINCT visitor) as visitors, MAX(created_at) as last_seen')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit);
    }

    /**
     * @return Collection<int, static>
     */
    public static function topPages(int $limit = 10, ?string $site = null): Collection
    {
        return static::topPagesQuery($limit, $site)->get();
    }

    /**
     * Asal pengunjung, diurutkan dari yang paling banyak mendatangkan orang.
     *
     * Hanya kunjungan halaman yang dihitung: laporan klik tombol WhatsApp
     * datang dari halaman yang sama, sehingga kalau ikut dihitung pengunjung
     * yang sama bisa muncul dua kali di diagram.
     *
     * @return Collection<int, static>
     */
    public static function sources(int $limit = 8, ?string $site = null): Collection
    {
        return static::query()
            ->pageViews()
            ->forSite($site)
            ->selectRaw('MAX(id) as id, source, COUNT(DISTINCT visitor) as visitors, COUNT(*) as views')
            ->groupBy('source')
            ->orderByDesc('visitors')
            ->limit($limit)
            ->get();
    }

    /**
     * Perangkat yang dipakai pengunjung.
     *
     * @return Collection<int, static>
     */
    public static function devices(?string $site = null): Collection
    {
        return static::query()
            ->pageViews()
            ->forSite($site)
            ->selectRaw('MAX(id) as id, device, COUNT(DISTINCT visitor) as visitors, COUNT(*) as views')
            ->groupBy('device')
            ->orderByDesc('visitors')
            ->get();
    }

    public static function deviceLabel(?string $device): string
    {
        return self::DEVICE_LABELS[$device] ?? 'Tidak dikenal';
    }

    /**
     * Jumlah per hari untuk satu jenis kejadian, dengan kunci tanggal
     * YYYY-MM-DD. Dikelompokkan dari kolom visited_on, jadi query-nya sama di
     * MySQL maupun SQLite.
     *
     * @return array<string, int>
     */
    private static function dailyTotals(Builder $query, Carbon $from, string $aggregate): array
    {
        $rows = $query
            ->between($from)
            ->selectRaw("visited_on as day, {$aggregate} as total")
            ->groupBy('visited_on')
            ->get();

        return $rows
            ->mapWithKeys(fn (self $row): array => [(string) $row->day => (int) $row->total])
            ->all();
    }
}
