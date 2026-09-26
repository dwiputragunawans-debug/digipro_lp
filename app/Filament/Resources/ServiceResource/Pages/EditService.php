<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ServiceResource;
use Filament\Actions;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
