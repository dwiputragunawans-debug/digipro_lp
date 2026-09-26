<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\PackageResource\Pages;
use App\Models\Lajukasir\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kartu pada bagian "Paket Harga" halaman LajuKasir.
 *
 * Harga ditulis sebagai teks, bukan angka, karena paket kasir lazim ditulis
 * "Rp 1.500.000" atau "Hubungi Kami" dan halaman ini tidak menghitungnya.
 */
class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $slug = 'lajukasir/paket';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Paket Harga';

    protected static ?string $modelLabel = 'Paket';

    protected static ?string $pluralModelLabel = 'Paket Harga';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 102;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama paket')
                    ->required()
                    ->placeholder('Toko'),
                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->placeholder('Rp 1.500.000')
                    ->helperText('Tulis apa adanya, misalnya "Rp 1.500.000" atau "Hubungi Kami".'),
                Forms\Components\TextInput::make('price_unit')
                    ->label('Keterangan harga')
                    ->placeholder('sekali bayar'),
                Forms\Components\TextInput::make('badge')
                    ->label('Label sudut')
                    ->helperText('Pita kecil di sudut kanan atas kartu, misalnya "TERLARIS". Kosongkan kalau tidak perlu.'),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('features')
                    ->label('Isi paket')
                    ->rows(6)
                    ->helperText('Tulis satu keunggulan per baris. Baris kosong diabaikan.')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_highlighted')
                    ->label('Paket utama')
                    ->helperText('Paket ini ditampilkan dengan gradasi teal yang lebih gelap supaya lebih menonjol.')
                    ->default(false),
                Forms\Components\TextInput::make('cta_text')
                    ->label('Tulisan tombol')
                    ->placeholder('Pilih Paket Ini'),
                Forms\Components\TextInput::make('cta_url')
                    ->label('Tautan tombol')
                    ->placeholder('Kosong berarti membuka WhatsApp')
                    ->helperText('Alamat tujuan tombol, misalnya https://wa.me/6281234567890.'),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu di halaman depan.')
                    ->required()
                    ->numeric()
                    ->default(0),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama paket')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge')
                    ->label('Label')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_highlighted')
                    ->label('Paket utama')
                    ->boolean(),
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
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
