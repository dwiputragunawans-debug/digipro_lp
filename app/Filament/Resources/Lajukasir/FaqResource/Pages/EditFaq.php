<?php

namespace App\Filament\Resources\Lajukasir\FaqResource\Pages;

use App\Filament\Resources\Lajukasir\FaqResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditFaq extends EditRecord
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
