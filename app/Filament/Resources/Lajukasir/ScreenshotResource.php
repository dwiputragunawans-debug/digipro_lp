<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\ScreenshotResource\Pages;
use App\Models\Lajukasir\Screenshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Tangkapan layar aplikasi pada bagian "Tampilan Aplikasi" halaman LajuKasir.
 */
class ScreenshotResource extends Resource
{
    protected static ?string $model = Screenshot::class;

    protected static ?string $slug = 'lajukasir/tampilan';

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?string $navigationLabel = 'Tampilan Aplikasi';

    protected static ?string $modelLabel = 'Tangkapan layar';

    protected static ?string $pluralModelLabel = 'Tampilan Aplikasi';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 104;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar')
                    ->image()
                    // Model membacanya dari disk publik, jadi unggahannya pun
                    // harus mendarat di sana.
                    ->disk('public')
                    ->columnSpanFull()
                    ->helperText('Tangkapan layar halaman aplikasi, misalnya layar kasir atau laporan.'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->placeholder('Halaman kasir'),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu di halaman depan.')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('caption')
                    ->label('Keterangan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar'),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->placeholder('-')
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
            'index' => Pages\ListScreenshots::route('/'),
            'create' => Pages\CreateScreenshot::route('/create'),
            'edit' => Pages\EditScreenshot::route('/{record}/edit'),
        ];
    }
}
