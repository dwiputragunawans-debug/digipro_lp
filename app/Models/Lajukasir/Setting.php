<?php

namespace App\Models\Lajukasir;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSiteBranding;
use App\Models\Concerns\HasWhatsApp;
use App\Support\TextLines;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Pengaturan halaman LajuKasir.
 *
 * Isinya sengaja dipisah dari App\Models\SiteSetting supaya admin bisa mengisi
 * DigiPro Solution dan LajuKasir tanpa saling menimpa, walau keduanya memakai
 * komponen logo, kartu obrolan WhatsApp dan tag SEO yang sama.
 *
 * Seperti site_settings, tabel ini hanya berisi satu baris dan dibuat otomatis
 * saat pertama kali dibuka di panel admin.
 */
class Setting extends Model
{
    use HasSeoMeta;
    use HasSiteBranding;
    use HasWhatsApp;

    protected $table = 'lajukasir_settings';

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
     * Nama yang dipakai selama admin belum mengisi kolom "Nama Situs".
     */
    public const DEFAULT_SITE_NAME = 'LajuKasir';

    /**
     * Slogan singkat di bawah logo.
     */
    public const DEFAULT_TAGLINE = 'Aplikasi Kasir Toko';

    /**
     * Tombol WhatsApp LajuKasir memakai nomor yang sama dengan DigiPro
     * Solution, supaya calon pembeli tetap terhubung ke satu admin yang sama.
     */
    public const DEFAULT_WHATSAPP_NUMBER = '6289662135974';

    public const DEFAULT_WHATSAPP_GREETING = 'Halo 👋 Mau tanya soal aplikasi kasir LajuKasir?';

    public const DEFAULT_WHATSAPP_MESSAGE = 'Halo, saya mau tanya soal aplikasi kasir LajuKasir.';

    /**
     * Akhiran judul halaman kalau admin belum mengisi judul SEO-nya:
     * "LajuKasir - Aplikasi Kasir Offline untuk Toko".
     */
    public const DEFAULT_META_TITLE_SUFFIX = 'Aplikasi Kasir untuk Toko';

    public const DEFAULT_META_DESCRIPTION = 'LajuKasir adalah aplikasi kasir (POS) untuk toko, kios dan warung. Bekerja tanpa internet, cetak struk ke printer thermal Bluetooth, kelola stok, piutang dan laporan penjualan dari satu aplikasi.';

    public const DEFAULT_META_KEYWORDS = 'aplikasi kasir, aplikasi kasir offline, software kasir, pos toko, aplikasi toko, cetak struk bluetooth, laporan penjualan, lajukasir';

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
     * LajuKasir settings are a singleton: there is only ever one row, and it is
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
            // Halaman tetap tersaji walau tabelnya belum ada, misalnya sebelum
            // `php artisan migrate` dijalankan.
            return static::$current = new static;
        }
    }

    public static function forgetCurrent(): void
    {
        static::$current = null;
    }

    /**
     * Nama situs, dengan nama bawaan LajuKasir sebelum admin mengisinya.
     */
    public function siteName(): string
    {
        return $this->site_name ?: static::DEFAULT_SITE_NAME;
    }

    /**
     * Slogan singkat yang tampil di bawah logo.
     */
    public function tagline(): string
    {
        return $this->brand_tagline ?: static::DEFAULT_TAGLINE;
    }

    /**
     * Alamat bawaan halaman LajuKasir: subdomainnya sendiri, bukan domain
     * utama DigiPro Solution.
     */
    protected function defaultSiteUrl(): string
    {
        return (string) config('lajukasir.url');
    }

    /**
     * Whether a hero image has been uploaded and is still present on the disk.
     */
    public function hasHeroImage(): bool
    {
        return filled($this->hero_image) && $this->heroImageFileExists($this->hero_image);
    }

    /**
     * Root relative URL of the uploaded hero image.
     */
    public function heroImageUrl(): ?string
    {
        return $this->hasHeroImage() ? Storage::disk('public')->url($this->hero_image) : null;
    }

    /**
     * Angka-angka di bagian pembuka, ditulis "5.000|Toko aktif" satu per baris.
     *
     * @return array<int, array{value: string, label: string}>
     */
    protected function heroStatsList(): Attribute
    {
        return Attribute::get(fn (): array => TextLines::pairs($this->hero_stats));
    }

    /**
     * Tautan WhatsApp tombol utama. Dikosongkan berarti memakai nomor WhatsApp
     * yang diatur di menu ini juga.
     */
    public function heroPrimaryUrl(): string
    {
        // Tautan WhatsApp dari admin dilengkapi pesannya lebih dahulu, supaya
        // pengunjung tidak mendarat di kolom chat yang kosong.
        return $this->whatsappUrlWithFilledText($this->hero_primary_url) ?: $this->whatsappUrl();
    }

    /**
     * Tautan tombol ajakan bertindak penutup, dengan nomor WhatsApp sebagai
     * cadangan supaya tombolnya tidak pernah menuju halaman kosong.
     */
    public function ctaButtonUrl(): string
    {
        return $this->whatsappUrlWithFilledText($this->cta_button_url) ?: $this->whatsappUrl();
    }

    protected function heroImageFileExists(?string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (Throwable) {
            return false;
        }
    }
}
