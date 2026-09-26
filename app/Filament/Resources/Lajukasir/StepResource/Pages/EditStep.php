<?php

namespace App\Filament\Resources\Lajukasir\StepResource\Pages;

use App\Filament\Resources\Lajukasir\StepResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditStep extends EditRecord
{
    protected static string $resource = StepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
