<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Halaman yang paling banyak dibuka, beserta jumlah pengunjungnya.
 */
class TopPagesTable extends TableWidget
{
    protected static ?int $sort = -8;

    protected int|string|array $columnSpan = 'full';

    /**
     * Situs yang dihitung widget ini. Null berarti DigiPro Solution, dan
     * dasbor LajuKasir menimpanya lewat App\Filament\Widgets\Lajukasir\*.
     */
    protected function site(): ?string
    {
        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Halaman yang paling banyak dibuka')
            ->description('Sepuluh halaman teratas sejak analitik mulai mencatat.')
            ->query(AnalyticsEvent::topPagesQuery(site: $this->site()))
            ->columns([
                TextColumn::make('path')
                    ->label('Halaman')
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Dibuka')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('visitors')
                    ->label('Pengunjung')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('last_seen')
                    ->label('Terakhir dibuka')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
            ])
            // Daftarnya sudah dibatasi sepuluh baris, jadi tidak perlu dicari
            // atau dipaginasi.
            ->paginated(false)
            ->emptyStateHeading('Belum ada kunjungan')
            ->emptyStateDescription('Angka akan muncul setelah ada pengunjung yang membuka landing page.');
    }
}
