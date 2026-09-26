<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\FaqResource\Pages;
use App\Models\Lajukasir\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Tanya jawab pada bagian "Pertanyaan Umum" halaman LajuKasir.
 */
class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $slug = 'lajukasir/faq';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Pertanyaan Umum';

    protected static ?string $modelLabel = 'Pertanyaan';

    protected static ?string $pluralModelLabel = 'Pertanyaan Umum';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 105;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->label('Pertanyaan')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('answer')
                    ->label('Jawaban')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Jawab dengan kalimat pendek; pertanyaan yang jawabannya kosong tetap tampil, tetapi kartunya terlihat menggantung.'),
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
                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->wrap(),
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
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
