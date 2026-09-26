<?php

namespace App\Support\Seo;

use App\Models\CtaSection;
use App\Models\Feature;
use App\Models\Hero;
use App\Models\Lajukasir\Faq as LajukasirFaq;
use App\Models\Lajukasir\Feature as LajukasirFeature;
use App\Models\Lajukasir\Package as LajukasirPackage;
use App\Models\Lajukasir\Screenshot as LajukasirScreenshot;
use App\Models\Lajukasir\Setting as LajukasirSetting;
use App\Models\Lajukasir\Step as LajukasirStep;
use App\Models\Lajukasir\Testimonial as LajukasirTestimonial;
use App\Models\Portfolio;
use App\Models\Process;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Isi sitemap.xml.
 *
 * Setiap situs hanya punya satu halaman publik, jadi daftarnya tetap: alamat
 * kanonis halaman utama, dengan tanggal perubahan terakhir yang diambil dari
 * konten yang diatur di panel admin.
 *
 * Dipakai bersama oleh DigiPro Solution dan halaman LajuKasir: pengaturan dan
 * daftar model kontennya dikirim sebagai parameter, dengan nilai bawaan
 * pengaturan DigiPro Solution supaya pemanggil lama tidak perlu diubah.
 */
class Sitemap
{
    /**
     * Model yang isinya tampil di landing page DigiPro Solution, dipakai untuk
     * mencari tanggal perubahan terakhir.
     *
     * @var list<class-string>
     */
    public const CONTENT_MODELS = [
        Hero::class,
        Feature::class,
        Service::class,
        Portfolio::class,
        Process::class,
        CtaSection::class,
        SiteSetting::class,
    ];

    /**
     * Model yang isinya tampil di halaman LajuKasir, dipakai untuk mencari
     * tanggal perubahan terakhir halaman itu.
     *
     * @var list<class-string>
     */
    public const LAJUKASIR_CONTENT_MODELS = [
        LajukasirFeature::class,
        LajukasirPackage::class,
        LajukasirStep::class,
        LajukasirScreenshot::class,
        LajukasirFaq::class,
        LajukasirTestimonial::class,
        LajukasirSetting::class,
    ];

    /**
     * @param  Model|null  $setting  Pengaturan situs yang dipakai. Bawaannya pengaturan DigiPro Solution.
     * @param  list<class-string>|null  $contentModels  Model yang isinya tampil di halaman. Bawaannya konten DigiPro Solution.
     * @return list<array{loc: string, lastmod: string, changefreq: string, priority: string}>
     */
    public static function urls(?Model $setting = null, ?array $contentModels = null): array
    {
        $setting ??= SiteSetting::current();
        $contentModels ??= self::CONTENT_MODELS;

        return [
            [
                'loc' => $setting->siteUrl().'/',
                'lastmod' => static::lastModified($contentModels)->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
        ];
    }

    /**
     * Tanggal perubahan terakhir dari seluruh konten halaman. Sebelum ada
     * konten sama sekali, hasilnya adalah waktu sekarang.
     *
     * @param  list<class-string>|null  $contentModels  Bawaannya konten DigiPro Solution.
     */
    public static function lastModified(?array $contentModels = null): Carbon
    {
        $contentModels ??= self::CONTENT_MODELS;

        try {
            $latest = collect($contentModels)
                ->map(fn (string $model): ?string => $model::query()->max('updated_at'))
                ->filter()
                ->max();

            return filled($latest) ? Carbon::parse($latest) : now();
        } catch (Throwable) {
            // Sitemap tetap tersaji walau tabelnya belum ada.
            return now();
        }
    }
}
