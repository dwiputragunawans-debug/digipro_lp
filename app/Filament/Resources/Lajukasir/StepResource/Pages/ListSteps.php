<?php

namespace App\Filament\Resources\Lajukasir\StepResource\Pages;

use App\Filament\Resources\Lajukasir\StepResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSteps extends ListRecords
{
    protected static string $resource = StepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
