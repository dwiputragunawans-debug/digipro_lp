<?php

namespace App\Filament\Widgets\Lajukasir;

use App\Filament\Widgets\VisitorStatsOverview as BaseWidget;
use App\Models\AnalyticsEvent;

/**
 * Ringkasan angka kunjungan halaman LajuKasir.
 *
 * Widgetnya sama persis dengan yang dipakai dasbor DigiPro Solution, hanya
 * angka yang dihitungnya saja yang dibatasi ke kejadian dari subdomain
 * LajuKasir.
 */
class VisitorStatsOverview extends BaseWidget
{
    /**
     * Hanya dipakai oleh halaman Analitik LajuKasir, supaya tidak muncul dua
     * kali di dasbor utama. Filament melewatkan widget yang tidak ditemukan
     * ulang, jadi kelasnya didaftarkan langsung oleh halaman itu.
     */
    protected static bool $isDiscovered = false;

    protected function site(): ?string
    {
        return AnalyticsEvent::SITE_LAJUKASIR;
    }
}
