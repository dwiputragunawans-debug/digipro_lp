<?php

namespace App\Filament\Widgets\Lajukasir;

use App\Filament\Widgets\TopPagesTable as BaseWidget;
use App\Models\AnalyticsEvent;

/**
 * Halaman LajuKasir yang paling banyak dibuka, lihat
 * App\Filament\Widgets\Lajukasir\VisitorStatsOverview.
 */
class TopPagesTable extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected function site(): ?string
    {
        return AnalyticsEvent::SITE_LAJUKASIR;
    }
}
