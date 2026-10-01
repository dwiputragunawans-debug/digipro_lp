<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Bagian merek dan alamat situs yang dipakai bersama oleh pengaturan DigiPro
 * Solution (App\Models\SiteSetting) dan pengaturan halaman LajuKasir
 * (App\Models\Lajukasir\Setting).
 *
 * Isinya sengaja dipindahkan ke trait supaya kedua situs memakai logika yang
 * sama: cara membaca logo di disk publik, cara menghitung rasio logo, dan cara
 * menentukan alamat kanonis.
 *
 * Model yang memakai trait ini wajib mendefinisikan konstanta
 * FALLBACK_LOGO_ASPECT_RATIO, dan boleh menimpa defaultSiteUrl() kalau alamat
 * bawaan situsnya bukan SITE_URL milik DigiPro Solution.
 */
trait HasSiteBranding
{
    /**
     * Jenis gambar favicon, dikenali dari akhiran berkasnya supaya tag <link>
     * bisa menyebut MIME type-nya dengan benar.
     *
     * @var array<string, string>
     */
    private const FAVICON_TYPE_BY_EXTENSION = [
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'avif' => 'image/avif',
    ];

    /**
     * Jenis gambar yang dipakai sebagai apple-touch-icon. Apple belum
     * menerima SVG di sana, dan ICO bukan format yang diharapkannya, jadi
     * keduanya dilewati dan tag-nya tidak perlu dirender.
     *
     * @var list<string>
     */
    private const APPLE_TOUCH_ICON_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    /**
     * Nama situs yang tampil di panel admin, judul halaman dan alt logo.
     */
    public function siteName(): string
    {
        return $this->site_name ?: config('app.name');
    }

    /**
     * Whether a logo has been uploaded and is still present on the public disk.
     */
    public function hasLogo(): bool
    {
        return filled($this->logo) && static::publicFileExists($this->logo);
    }

    /**
     * Root relative URL of the uploaded logo, so that it is always loaded from
     * the same origin as the page that renders it.
     */
    public function logoUrl(): ?string
    {
        return $this->hasLogo() ? Storage::disk('public')->url($this->logo) : null;
    }

    /**
     * Width / height ratio of the uploaded logo. The adaptive logo is rendered
     * at a fixed height, so its ratio keeps it from being squashed or from
     * leaving a big gap around it.
     */
    public function logoAspectRatio(): float
    {
        return static::aspectRatioFor($this->logo);
    }

    public static function aspectRatioFor(?string $path): float
    {
        if (! filled($path)) {
            return static::FALLBACK_LOGO_ASPECT_RATIO;
        }

        try {
            $file = Storage::disk('public')->path($path);

            if (! is_file($file)) {
                return static::FALLBACK_LOGO_ASPECT_RATIO;
            }

            $size = @getimagesize($file);

            $ratio = ($size && $size[0] && $size[1])
                ? $size[0] / $size[1]
                : static::aspectRatioFromSvg($file);

            if (! $ratio) {
                return static::FALLBACK_LOGO_ASPECT_RATIO;
            }

            return round(max(0.2, min(10.0, (float) $ratio)), 4);
        } catch (Throwable) {
            return static::FALLBACK_LOGO_ASPECT_RATIO;
        }
    }

    protected static function publicFileExists(?string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Read the dimensions out of an SVG, which getimagesize() does not support.
     */
    protected static function aspectRatioFromSvg(string $file): ?float
    {
        $svg = @file_get_contents($file, false, null, 0, 4096);

        if (! is_string($svg)) {
            return null;
        }

        if (preg_match('/viewBox\s*=\s*["\']\s*[-\d.]+[,\s]+[-\d.]+[,\s]+([\d.]+)[,\s]+([\d.]+)/i', $svg, $matches)) {
            return ((float) $matches[2]) > 0 ? ((float) $matches[1]) / ((float) $matches[2]) : null;
        }

        if (
            preg_match('/<svg[^>]*\swidth\s*=\s*["\']([\d.]+)/i', $svg, $width) &&
            preg_match('/<svg[^>]*\sheight\s*=\s*["\']([\d.]+)/i', $svg, $height)
        ) {
            return ((float) $height[1]) > 0 ? ((float) $width[1]) / ((float) $height[1]) : null;
        }

        return null;
    }

    /**
     * Berkas favicon yang dipakai situs ini.
     *
     * Favicon yang diunggah khusus menang. Selama belum ada, logonya yang
     * dipakai, sehingga ikon di tab browser sudah terisi begitu admin
     * mengunggah logo. Favicon tersendiri tetap berguna karena logo biasanya
     * terlalu detail untuk dibaca sekecil ikon tab.
     */
    public function faviconPath(): ?string
    {
        $favicon = $this->favicon ?? null;

        if (filled($favicon) && static::publicFileExists($favicon)) {
            return (string) $favicon;
        }

        return $this->hasLogo() ? (string) $this->logo : null;
    }

    /**
     * Whether there is an icon to render, either its own or the logo it falls
     * back to.
     */
    public function hasFavicon(): bool
    {
        return filled($this->faviconPath());
    }

    /**
     * Root relative URL of the favicon, so that it is always loaded from the
     * same origin as the page that renders it.
     */
    public function faviconUrl(): ?string
    {
        $path = $this->faviconPath();

        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    /**
     * MIME type favicon. Berkas dengan akhiran yang tidak dikenal dikembalikan
     * sebagai null, jadi tag <link>-nya dibiarkan tanpa type dan browser yang
     * menebak sendiri.
     */
    public function faviconType(): ?string
    {
        $extension = strtolower(pathinfo((string) $this->faviconPath(), PATHINFO_EXTENSION));

        return self::FAVICON_TYPE_BY_EXTENSION[$extension] ?? null;
    }

    /**
     * Alamat ikon untuk perangkat Apple, atau null kalau berkasnya bukan jenis
     * gambar yang diterima di sana.
     */
    public function appleTouchIconUrl(): ?string
    {
        $path = $this->faviconPath();

        if (! filled($path) || ! in_array($this->faviconType(), self::APPLE_TOUCH_ICON_TYPES, true)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Alamat situs kalau kolom "Alamat Situs" di panel admin masih kosong.
     *
     * Bawaannya SITE_URL milik DigiPro Solution; halaman LajuKasir menimpanya
     * dengan alamat subdomainnya sendiri.
     */
    protected function defaultSiteUrl(): string
    {
        return (string) config('seo.url');
    }

    /**
     * Alamat kanonis situs, tanpa garis miring di akhir.
     *
     * Kolom "Alamat Situs" di panel admin menang atas alamat bawaan di berkas
     * .env, sehingga satu database bisa dipakai untuk staging dan produksi.
     */
    public function siteUrl(): string
    {
        $url = filled($this->site_url) ? $this->site_url : $this->defaultSiteUrl();

        return rtrim((string) ($url ?: config('app.url')), '/');
    }

    /**
     * Alamat yang dipakai tag canonical, og:url dan sitemap.xml.
     */
    public function canonicalUrl(): string
    {
        return $this->siteUrl();
    }
}
