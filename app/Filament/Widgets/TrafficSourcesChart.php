<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Support\Analytics\ChartPalette;
use Filament\Widgets\ChartWidget;

/**
 * Diagram asal pengunjung: dari mesin pencari, media sosial atau langsung.
 *
 * Setiap potongan diberi warnanya sendiri, karena Chart.js kalau tidak diatur
 * akan mewarnai seluruh diagram dengan satu warna.
 */
class TrafficSourcesChart extends ChartWidget
{
    protected static ?string $heading = 'Asal pengunjung';

    protected static ?string $description = 'Dari mana pengunjung menemukan situs ini.';

    protected static ?int $sort = -7;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $sources = AnalyticsEvent::sources(site: $this->site());

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung',
                    'data' => $sources->pluck('visitors')->map(fn ($visitors): int => (int) $visitors)->all(),
                    'backgroundColor' => ChartPalette::colors($sources->count()),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $sources->pluck('source')->map(fn ($source): string => (string) ($source ?: AnalyticsEvent::DIRECT_SOURCE))->all(),
        ];
    }

    /**
     * Situs yang dihitung widget ini. Null berarti DigiPro Solution, dan
     * dasbor LajuKasir menimpanya lewat App\Filament\Widgets\Lajukasir\*.
     */
    protected function site(): ?string
    {
        return null;
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
