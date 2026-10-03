<?php

namespace App\Models\Concerns;

use App\Support\Social\Platforms;

/**
 * Ikon media sosial di kaki halaman, dipakai bersama oleh pengaturan DigiPro
 * Solution (App\Models\SiteSetting) dan pengaturan halaman LajuKasir
 * (App\Models\Lajukasir\Setting).
 *
 * Hanya media sosial yang alamatnya diisi admin yang ikut dirender, sehingga
 * tidak ada ikon yang menuju halaman kosong. Urutannya mengikuti daftar di
 * App\Support\Social\Platforms supaya kedua situs tampil sama.
 */
trait HasSocialLinks
{
    /**
     * Media sosial yang siap ditampilkan, sudah lengkap dengan gambar ikonnya.
     *
     * @return array<int, array{key: string, label: string, url: string, icon: string, is_whatsapp: bool}>
     */
    public function socialLinks(): array
    {
        $links = [];

        foreach (Platforms::ALL as $key => $platform) {
            $url = Platforms::normalizeUrl($this->{Platforms::column($key)} ?? null);

            if ($url === null) {
                continue;
            }

            $links[] = [
                'key' => $key,
                'label' => $platform['label'],
                'url' => $url,
                'icon' => $platform['icon'],
                // Tautan WhatsApp ikut terhitung di analitik, sama seperti
                // tombol dan kartu obrolannya.
                'is_whatsapp' => $key === 'whatsapp',
            ];
        }

        return $links;
    }

    /**
     * Apakah ada ikon media sosial yang perlu dirender.
     */
    public function hasSocialLinks(): bool
    {
        return $this->socialLinks() !== [];
    }
}
