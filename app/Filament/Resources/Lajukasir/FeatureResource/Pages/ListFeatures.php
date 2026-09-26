<?php

namespace App\Filament\Resources\Lajukasir\FeatureResource\Pages;

use App\Filament\Resources\Lajukasir\FeatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
