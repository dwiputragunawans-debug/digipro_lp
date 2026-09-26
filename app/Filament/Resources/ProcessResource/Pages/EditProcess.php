<?php

namespace App\Filament\Resources\ProcessResource\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProcessResource;
use Filament\Actions;

class EditProcess extends EditRecord
{
    protected static string $resource = ProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
