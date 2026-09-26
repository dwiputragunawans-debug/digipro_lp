<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Lajukasir;
use Filament\Pages\Page;

/**
 * Kunjungan halaman LajuKasir.
 *
 * Widgetnya sama dengan yang dipakai dasbor DigiPro Solution
 * (App\Filament\Widgets\*), hanya angka yang dihitungnya saja yang dibatasi ke
 * kejadian dari subdomain LajuKasir, lihat AnalyticsEvent::scopeForSite().
 *
 * Halaman ini berdiri sendiri supaya angka DigiPro Solution dan LajuKasir tidak
 * saling bercampur walau keduanya sama-sama memakai path "/".
 */
class LajukasirAnalytics extends Page
{
    protected static string $view = 'filament.pages.lajukasir-analytics';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Analitik LajuKasir';

    protected static ?string $title = 'Analitik LajuKasir';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 107;

    public function getSubheading(): ?string
    {
        return 'Kunjungan halaman LajuKasir saja. Angka piksel dan klik di halaman DigiPro Solution tidak ikut dihitung di sini.';
    }

    /**
     * @return array<int, class-string>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            Lajukasir\VisitorStatsOverview::class,
        ];
    }

    /**
     * @return array<int, class-string>
     */
    protected function getFooterWidgets(): array
    {
        return [
            Lajukasir\VisitorChart::class,
            Lajukasir\TopPagesTable::class,
            Lajukasir\TrafficSourcesChart::class,
            Lajukasir\DeviceBreakdownChart::class,
        ];
    }
}
