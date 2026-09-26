<?php

namespace App\Filament\Widgets\Lajukasir;

use App\Filament\Widgets\VisitorChart as BaseWidget;
use App\Models\AnalyticsEvent;

/**
 * Grafik harian halaman LajuKasir, lihat
 * App\Filament\Widgets\Lajukasir\VisitorStatsOverview.
 */
class VisitorChart extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function site(): ?string
    {
        return AnalyticsEvent::SITE_LAJUKASIR;
    }
}
