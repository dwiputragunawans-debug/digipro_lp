<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroResource\Pages;
use App\Models\Hero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroResource extends Resource
{
    protected static ?string $model = Hero::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Banner Hero';

    protected static ?string $modelLabel = 'Banner Hero';

    protected static ?string $pluralModelLabel = 'Banner Hero';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Forms\Components\Textarea::make('subtitle')
                    ->label('Subjudul')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('background_image')
                    ->label('Gambar latar')
                    ->image()
                    ->helperText('Pakai gambar beresolusi lebar, misalnya 1920 x 1080 piksel.'),
                Forms\Components\TextInput::make('cta_primary_text')
                    ->label('Teks tombol utama'),
                Forms\Components\TextInput::make('cta_primary_url')
                    ->label('Tautan tombol utama')
                    ->helperText('Boleh berupa tautan lengkap atau bagian di halaman, misalnya #kontak.'),
                Forms\Components\TextInput::make('cta_secondary_text')
                    ->label('Teks tombol kedua'),
                Forms\Components\TextInput::make('cta_secondary_url')
                    ->label('Tautan tombol kedua')
                    ->helperText('Boleh berupa tautan lengkap atau bagian di halaman, misalnya #solusi.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('background_image')
                    ->label('Gambar latar'),
                Tables\Columns\TextColumn::make('cta_primary_text')
                    ->label('Teks tombol utama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cta_primary_url')
                    ->label('Tautan tombol utama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cta_secondary_text')
                    ->label('Teks tombol kedua')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cta_secondary_url')
                    ->label('Tautan tombol kedua')
                    ->searchable(),
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
            'index' => Pages\ListHeroes::route('/'),
            'create' => Pages\CreateHero::route('/create'),
            'edit' => Pages\EditHero::route('/{record}/edit'),
        ];
    }
}
