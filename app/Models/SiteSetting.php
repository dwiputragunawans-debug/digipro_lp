<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSiteBranding;
use App\Models\Concerns\HasSocialLinks;
use App\Models\Concerns\HasWhatsApp;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Pengaturan landing page DigiPro Solution.
 *
 * Logika merek, tombol WhatsApp, ikon media sosial dan tag SEO-nya dipakai
 * bersama dengan pengaturan halaman LajuKasir (App\Models\Lajukasir\Setting)
 * lewat trait di App\Models\Concerns, supaya kedua situs berperilaku sama
 * persis dan hanya nilai bawaannya yang berbeda.
 */
class SiteSetting extends Model
{
    use HasSeoMeta;
    use HasSiteBranding;
    use HasSocialLinks;
    use HasWhatsApp;

    /**
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'logo_adaptive' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'robots_index' => 'boolean',
    ];

    /**
     * Width / height ratio used when the logo dimensions cannot be read.
     */
    protected const FALLBACK_LOGO_ASPECT_RATIO = 3.0;

    /**
     * Nomor WhatsApp yang sudah dipakai situs ini, sehingga tombolnya tetap
     * berfungsi sebelum admin mengisi nomornya di menu Pengaturan Situs.
     */
    public const DEFAULT_WHATSAPP_NUMBER = '6289662133959';

    /**
     * Sapaan di balon obrolan kartu WhatsApp.
     */
    public const DEFAULT_WHATSAPP_GREETING = 'Halo 👋 Anda butuh website?';

    /**
     * Pesan yang sudah terisi di kolom chat WhatsApp pengunjung.
     */
    public const DEFAULT_WHATSAPP_MESSAGE = 'Halo, saya butuh website untuk usaha saya.';

    /**
     * Akhiran judul halaman kalau admin belum mengisi judul SEO-nya:
     * "Nama Situs - Solusi Digital UMKM".
     */
    public const DEFAULT_META_TITLE_SUFFIX = 'Solusi Digital UMKM';

    /**
     * Deskripsi bawaan yang dipakai mesin pencari dan pratinjau tautan selama
     * admin belum menulis deskripsinya sendiri.
     */
    public const DEFAULT_META_DESCRIPTION = 'DigiPro Solution membuat website profesional untuk UMKM: cepat, mobile friendly, mudah dikelola dan siap mendatangkan pelanggan. Konsultasi gratis.';

    /**
     * Kata kunci bawaan, ditulis dengan pemisah koma seperti di panel admin.
     */
    public const DEFAULT_META_KEYWORDS = 'jasa pembuatan website, website umkm, website perusahaan, toko online, aplikasi web custom, digipro solution';

    /**
     * The settings row, resolved once per request.
     */
    protected static ?self $current = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCurrent());
        static::deleted(fn () => static::forgetCurrent());
    }

    /**
     * Site settings are a singleton: there is only ever one row, and it is
     * created on demand so the admin form always has a record to save into.
     */
    public static function current(): self
    {
        if (static::$current instanceof self) {
            return static::$current;
        }

        try {
            return static::$current = static::query()->firstOrCreate([]);
        } catch (Throwable) {
            // Never break a page just because the settings table is missing,
            // for example before `php artisan migrate` has been run.
            return static::$current = new static;
        }
    }

    public static function forgetCurrent(): void
    {
        static::$current = null;
    }
}
