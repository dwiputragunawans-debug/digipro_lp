<?php

namespace App\Filament\Widgets\Lajukasir;

use App\Filament\Widgets\DeviceBreakdownChart as BaseWidget;
use App\Models\AnalyticsEvent;

/**
 * Perangkat pengunjung halaman LajuKasir, lihat
 * App\Filament\Widgets\Lajukasir\VisitorStatsOverview.
 */
class DeviceBreakdownChart extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function site(): ?string
    {
        return AnalyticsEvent::SITE_LAJUKASIR;
    }
}
