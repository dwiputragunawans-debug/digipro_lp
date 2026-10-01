<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Support\Heroicons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Layanan';

    protected static ?string $modelLabel = 'Layanan';

    protected static ?string $pluralModelLabel = 'Layanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('icon')
                    ->label('Ikon')
                    ->options(fn (?Service $record): array => Heroicons::optionsWithIcons($record?->icon))
                    ->allowHtml()
                    ->searchable()
                    ->default(Heroicons::FALLBACK)
                    ->helperText('Ikon yang tampil di kartu bagian "Solusi Digital untuk UMKM". Nama ikon Heroicons-nya ditulis dalam tanda kurung, jadi "Petir, cepat (bolt)" sama dengan heroicon-o-bolt.'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('badge')
                    ->label('Label sudut')
                    ->helperText('Pita kecil di sudut kanan atas kartu, misalnya "POPULER". Kosongkan kalau tidak perlu.'),
                Forms\Components\Toggle::make('is_highlighted')
                    ->label('Kartu utama')
                    ->helperText('Kartu ini ditampilkan dengan latar gelap supaya lebih menonjol.')
                    ->default(false),
                Forms\Components\Textarea::make('benefits')
                    ->label('Keunggulan')
                    ->helperText('Tulis satu keunggulan per baris. Baris kosong diabaikan.')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cta_text')
                    ->label('Tulisan tombol')
                    ->placeholder('Selengkapnya')
                    ->helperText('Dikosongkan berarti "Selengkapnya", atau "Hubungi Kami" untuk kartu utama.'),
                Forms\Components\TextInput::make('cta_url')
                    ->label('Tautan tombol')
                    ->placeholder('#')
                    ->helperText('Alamat tujuan tombol, misalnya https://wa.me/6281234567890. Tautan WhatsApp boleh dikosongkan: tombolnya lalu otomatis menuju WhatsApp dengan pesan di bawah.'),
                Forms\Components\Textarea::make('whatsapp_message')
                    ->label('Pesan WhatsApp untuk paket ini')
                    ->rows(3)
                    ->maxLength(600)
                    ->placeholder(Service::DEFAULT_WHATSAPP_MESSAGE_TEMPLATE)
                    ->helperText('Teks ini sudah terisi di kolom chat WhatsApp saat pengunjung menekan tombol kartu ini, jadi admin tahu paket mana yang ditanyakan. Tulis {paket} di dalamnya untuk meletakkan nama paketnya. Kosong berarti memakai pesan bawaan.')
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
                    ->formatStateUsing(fn (?string $state): string => Heroicons::normalize($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badge')
                    ->label('Label')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_highlighted')
                    ->label('Kartu utama')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order_column')
                    ->label('Urutan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
