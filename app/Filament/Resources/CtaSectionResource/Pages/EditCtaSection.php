<?php

namespace App\Filament\Resources\CtaSectionResource\Pages;

use App\Filament\Resources\CtaSectionResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCtaSection extends EditRecord
{
    protected static string $resource = CtaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
