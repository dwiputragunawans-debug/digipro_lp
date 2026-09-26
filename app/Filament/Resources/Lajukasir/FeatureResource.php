<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\FeatureResource\Pages;
use App\Models\Lajukasir\Feature;
use App\Support\Heroicons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kartu pada bagian "Fitur Unggulan" halaman LajuKasir.
 */
class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static ?string $slug = 'lajukasir/fitur';

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationLabel = 'Fitur Unggulan';

    protected static ?string $modelLabel = 'Fitur';

    protected static ?string $pluralModelLabel = 'Fitur Unggulan';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 101;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('icon')
                    ->label('Ikon')
                    ->options(fn (?Feature $record): array => Heroicons::optionsWithIcons($record?->icon))
                    ->allowHtml()
                    ->searchable()
                    ->default(Heroicons::FALLBACK)
                    ->helperText('Ikon yang tampil di kartu fitur. Nama ikon Heroicons-nya ditulis dalam tanda kurung, jadi "Petir, cepat (bolt)" sama dengan heroicon-o-bolt.'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu di halaman depan.')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('icon')
                    ->label('Ikon')
                    ->icon(fn (?string $state): string => Heroicons::normalize($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_column')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('order_column')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
        ];
    }
}
