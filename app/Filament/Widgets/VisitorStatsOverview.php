<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan angka kunjungan di bagian atas dasbor.
 *
 * Angka utamanya adalah pengunjung unik per hari; jumlah halaman yang dibuka
 * dan jumlah orang yang mengklik tombol WhatsApp ditampilkan sebagai keterangan.
 */
class VisitorStatsOverview extends StatsOverviewWidget
{
    /**
     * Widget analitik tampil paling atas di dasbor.
     */
    protected static ?int $sort = -10;

    protected function getStats(): array
    {
        $days = AnalyticsEvent::days();
        $today = AnalyticsEvent::today();
        $rangeStart = AnalyticsEvent::rangeStart($days);

        // Null berarti situs DigiPro Solution; dasbor LajuKasir menimpanya.
        $site = $this->site();

        $visitorsToday = AnalyticsEvent::uniqueVisitors($today, null, $site);
        $viewsToday = AnalyticsEvent::viewCount($today, null, $site);
        $visitorsRange = AnalyticsEvent::uniqueVisitors($rangeStart, null, $site);
        $viewsRange = AnalyticsEvent::viewCount($rangeStart, null, $site);
        $clicksRange = AnalyticsEvent::whatsAppClickCount($rangeStart, null, $site);
        $clickingVisitors = AnalyticsEvent::whatsAppClickVisitors($rangeStart, null, $site);
        $topPage = AnalyticsEvent::topPages(1, $site)->first()?->path;

        return [
            Stat::make('Pengunjung hari ini', $this->number($visitorsToday))
                ->description($this->number($viewsToday).' halaman dibuka')
                ->descriptionIcon('heroicon-m-eye')
                ->chart(AnalyticsEvent::perDay(7, $site)['visitors'])
                ->color('primary'),
            Stat::make('Pengunjung '.$days.' hari terakhir', $this->number($visitorsRange))
                ->description('Rata-rata '.$this->number((int) round($visitorsRange / $days)).' orang per hari')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Halaman dibuka ('.$days.' hari)', $this->number($viewsRange))
                ->description($topPage ? 'Terbanyak: '.$topPage : 'Belum ada kunjungan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Klik WhatsApp ('.$days.' hari)', $this->number($clicksRange))
                ->description($this->number($clickingVisitors).' pengunjung menghubungi')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('success'),
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

    /**
     * Angka dengan pemisah ribuan gaya Indonesia: 1.234.
     */
    protected function number(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
