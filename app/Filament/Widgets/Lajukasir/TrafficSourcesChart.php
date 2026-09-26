<?php

namespace App\Filament\Widgets\Lajukasir;

use App\Filament\Widgets\TrafficSourcesChart as BaseWidget;
use App\Models\AnalyticsEvent;

/**
 * Asal pengunjung halaman LajuKasir, lihat
 * App\Filament\Widgets\Lajukasir\VisitorStatsOverview.
 */
class TrafficSourcesChart extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function site(): ?string
    {
        return AnalyticsEvent::SITE_LAJUKASIR;
    }
}
