<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Grafik harian selama 30 hari terakhir: pengunjung unik, halaman yang dibuka
 * dan klik tombol WhatsApp.
 *
 * Warnanya ditulis langsung di dataset karena Chart.js hanya memakai satu warna
 * bawaan dari tema, sehingga ketiga garisnya tidak bisa dibedakan.
 */
class VisitorChart extends ChartWidget
{
    protected static ?string $description = 'Pengunjung dihitung sebagai orang berbeda per hari, bukan jumlah halaman yang dibuka.';

    protected static ?int $sort = -9;

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    /**
     * Judulnya mengikuti jumlah hari yang diatur di config/analytics.php.
     */
    public function getHeading(): string|Htmlable|null
    {
        return 'Tren '.AnalyticsEvent::days().' hari terakhir';
    }

    protected function getData(): array
    {
        $data = AnalyticsEvent::perDay(site: $this->site());

        return [
            'datasets' => [
                [
                    'label' => 'Pengunjung',
                    'data' => $data['visitors'],
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Halaman dibuka',
                    'data' => $data['views'],
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Klik WhatsApp',
                    'data' => $data['clicks'],
                    'borderColor' => '#25d366',
                    'backgroundColor' => 'rgba(37, 211, 102, 0.2)',
                    'tension' => 0.35,
                ],
            ],
            'labels' => $data['labels'],
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
        return 'line';
    }
}
