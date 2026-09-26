<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessResource\Pages;
use App\Models\Process;
use App\Support\Heroicons;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProcessResource extends Resource
{
    protected static ?string $model = Process::class;

    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';

    protected static ?string $navigationLabel = 'Proses';

    protected static ?string $modelLabel = 'Proses';

    protected static ?string $pluralModelLabel = 'Proses';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('icon')
                    ->label('Ikon')
                    ->options(fn (?Process $record): array => Heroicons::optionsWithIcons($record?->icon))
                    ->allowHtml()
                    ->searchable()
                    ->helperText('Ikon di dalam lingkaran langkah. Boleh dikosongkan: kalau kosong yang tampil adalah nomor langkah di halaman depan.'),
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('order_column')
                    ->label('Urutan')
                    ->helperText('Angka lebih kecil tampil lebih dahulu, sesuai langkah di halaman depan.')
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
                    ->icon(fn (?string $state): ?string => Heroicons::resolve($state))
                    ->formatStateUsing(fn (?string $state): ?string => Heroicons::resolve($state))
                    ->placeholder('Nomor langkah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
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
            'index' => Pages\ListProcesses::route('/'),
            'create' => Pages\CreateProcess::route('/create'),
            'edit' => Pages\EditProcess::route('/{record}/edit'),
        ];
    }
}
