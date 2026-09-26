<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\StepResource\Pages;
use App\Models\Lajukasir\Step;
use App\Support\Heroicons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Langkah pada bagian "Cara Mulai" halaman LajuKasir.
 */
class StepResource extends Resource
{
    protected static ?string $model = Step::class;

    protected static ?string $slug = 'lajukasir/langkah';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Cara Mulai';

    protected static ?string $modelLabel = 'Langkah';

    protected static ?string $pluralModelLabel = 'Cara Mulai';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 103;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('icon')
                    ->label('Ikon')
                    ->options(fn (?Step $record): array => Heroicons::optionsWithIcons($record?->icon))
                    ->allowHtml()
                    ->searchable()
                    ->helperText('Kosongkan supaya lingkaran langkahnya menampilkan nomor urutnya saja.'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul langkah')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu, dan nomor langkahnya mengikuti urutan ini.')
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
                    ->placeholder('Nomor urut')
                    ->icon(fn (?string $state): ?string => Heroicons::resolve($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul langkah')
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
            'index' => Pages\ListSteps::route('/'),
            'create' => Pages\CreateStep::route('/create'),
            'edit' => Pages\EditStep::route('/{record}/edit'),
        ];
    }
}
