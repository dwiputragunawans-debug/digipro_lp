<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\TestimonialResource\Pages;
use App\Models\Lajukasir\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Testimoni pemilik toko pada bagian "Kata Pemilik Toko" halaman LajuKasir.
 */
class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $slug = 'lajukasir/testimoni';

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Testimoni';

    protected static ?string $modelLabel = 'Testimoni';

    protected static ?string $pluralModelLabel = 'Testimoni';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 106;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama pemilik')
                    ->required(),
                Forms\Components\TextInput::make('store')
                    ->label('Nama toko')
                    ->placeholder('Warung Bu Sri'),
                Forms\Components\FileUpload::make('photo')
                    ->label('Foto')
                    ->image()
                    // Testimonial resolves the photo through the public disk,
                    // so the upload has to land there as well.
                    ->disk('public')
                    ->helperText('Opsional. Kalau kosong, kartunya menampilkan inisial nama saja.'),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu di halaman depan.')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('quote')
                    ->label('Testimoni')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama pemilik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('store')
                    ->label('Nama toko')
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
            'index' => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
