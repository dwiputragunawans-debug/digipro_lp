<?php

namespace App\Support\Seo;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;

/**
 * Isi robots.txt.
 *
 * Kalau admin mematikan "Izinkan mesin pencari", seluruh situs ditutup untuk
 * crawler supaya halaman yang belum siap tidak terindeks. Panel admin selalu
 * ditutup, terlepas dari pilihan itu.
 *
 * Dipakai bersama oleh DigiPro Solution dan halaman LajuKasir; pengaturannya
 * dikirim sebagai parameter dengan pengaturan DigiPro Solution sebagai bawaan.
 */
class Robots
{
    public static function content(?Model $setting = null): string
    {
        $setting ??= SiteSetting::current();

        $lines = [
            'User-agent: *',
            'Disallow: /admin',
        ];

        if ($setting->isIndexable()) {
            $lines[] = 'Allow: /';
            $lines[] = '';
            $lines[] = 'Sitemap: '.$setting->siteUrl().'/sitemap.xml';
        } else {
            // Menutup seluruh situs, termasuk halaman yang sudah disebut di atas.
            $lines[] = 'Disallow: /';
        }

        return implode("\n", $lines)."\n";
    }
}
