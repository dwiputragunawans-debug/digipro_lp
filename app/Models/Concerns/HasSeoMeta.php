<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Tag SEO yang dipakai bersama oleh pengaturan DigiPro Solution
 * (App\Models\SiteSetting) dan pengaturan halaman LajuKasir
 * (App\Models\Lajukasir\Setting).
 *
 * Semuanya punya nilai bawaan yang masuk akal, jadi halaman tetap punya judul
 * dan deskripsi sebelum admin mengisinya di panel admin.
 *
 * Trait ini memanggil siteName() dan siteUrl()/absoluteUrl(), jadi model yang
 * memakainya juga wajib memakai App\Models\Concerns\HasSiteBranding, dan wajib
 * mendefinisikan konstanta DEFAULT_META_TITLE_SUFFIX, DEFAULT_META_DESCRIPTION
 * serta DEFAULT_META_KEYWORDS sendiri.
 */
trait HasSeoMeta
{
    /**
     * Judul halaman di tab browser dan di hasil pencarian.
     */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->siteName().' - '.static::DEFAULT_META_TITLE_SUFFIX;
    }

    /**
     * Deskripsi halaman untuk mesin pencari dan pratinjau tautan.
     */
    public function metaDescription(): string
    {
        return $this->meta_description ?: static::DEFAULT_META_DESCRIPTION;
    }

    /**
     * Kata kunci, dipecah dari kolom yang isinya dipisah koma.
     *
     * @return list<string>
     */
    public function metaKeywords(): array
    {
        $keywords = preg_split('/,/', (string) $this->meta_keywords) ?: [];

        $keywords = array_values(array_unique(array_filter(array_map('trim', $keywords))));

        return $keywords ?: explode(', ', static::DEFAULT_META_KEYWORDS);
    }

    /**
     * Whether a social preview image has been uploaded and is still present on
     * the public disk.
     */
    public function hasOgImage(): bool
    {
        return filled($this->og_image) && $this->ogImageFileExists($this->og_image);
    }

    /**
     * Root relative URL of the uploaded social preview image, or null when
     * there is none, in which case the preview falls back to the title and
     * description alone.
     */
    public function ogImageUrl(): ?string
    {
        return $this->hasOgImage() ? Storage::disk('public')->url($this->og_image) : null;
    }

    /**
     * Whether search engines may index the site. A missing column (for example
     * before migrating) keeps the site indexable.
     */
    public function isIndexable(): bool
    {
        return $this->robots_index === null || (bool) $this->robots_index;
    }

    /**
     * Ubah URL relatif dari disk publik menjadi URL absolut, karena tag
     * og:image dan data terstruktur harus berisi alamat lengkap.
     */
    public function absoluteUrl(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : $this->siteUrl().'/'.ltrim($url, '/');
    }

    /**
     * Pemeriksaan berkas yang berdiri sendiri, supaya trait ini tidak bergantung
     * pada urutan pemakaian trait lain di kelasnya.
     */
    protected function ogImageFileExists(?string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (Throwable) {
            return false;
        }
    }
}
