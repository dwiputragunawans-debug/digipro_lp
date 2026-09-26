<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CtaSectionResource\Pages;
use App\Models\CtaSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CtaSectionResource extends Resource
{
    protected static ?string $model = CtaSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Bagian CTA';

    protected static ?string $modelLabel = 'Bagian CTA';

    protected static ?string $pluralModelLabel = 'Bagian CTA';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('button_text')
                    ->label('Teks tombol'),
                Forms\Components\TextInput::make('button_url')
                    ->label('Tautan tombol')
                    ->helperText('Boleh berupa tautan lengkap atau bagian di halaman, misalnya #kontak.'),
                Forms\Components\TextInput::make('disclaimer')
                    ->label('Keterangan tambahan')
                    ->helperText('Catatan kecil di bawah tombol, boleh dikosongkan.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('button_text')
                    ->label('Teks tombol')
                    ->searchable(),
                Tables\Columns\TextColumn::make('button_url')
                    ->label('Tautan tombol')
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
            'index' => Pages\ListCtaSections::route('/'),
            'create' => Pages\CreateCtaSection::route('/create'),
            'edit' => Pages\EditCtaSection::route('/{record}/edit'),
        ];
    }
}
