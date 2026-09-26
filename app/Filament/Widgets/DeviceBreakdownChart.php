<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Support\Analytics\ChartPalette;
use Filament\Widgets\ChartWidget;

/**
 * Diagram perangkat yang dipakai pengunjung: ponsel, tablet atau desktop.
 *
 * Berguna untuk memutuskan tampilan mana yang perlu diprioritaskan; untuk
 * landing page seperti ini, sebagian besar pengunjung biasanya memakai ponsel.
 */
class DeviceBreakdownChart extends ChartWidget
{
    protected static ?string $heading = 'Perangkat pengunjung';

    protected static ?string $description = 'Perangkat yang dipakai untuk membuka situs.';

    protected static ?int $sort = -6;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $devices = AnalyticsEvent::devices(site: $this->site());

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung',
                    'data' => $devices->pluck('visitors')->map(fn ($visitors): int => (int) $visitors)->all(),
                    'backgroundColor' => ChartPalette::colors($devices->count()),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $devices->pluck('device')->map(fn ($device): string => AnalyticsEvent::deviceLabel($device))->all(),
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
