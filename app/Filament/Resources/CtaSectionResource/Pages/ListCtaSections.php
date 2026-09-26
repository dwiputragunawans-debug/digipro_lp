<?php

namespace App\Filament\Resources\CtaSectionResource\Pages;

use App\Filament\Resources\CtaSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCtaSections extends ListRecords
{
    protected static string $resource = CtaSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
