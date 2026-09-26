<?php

namespace App\Filament\Resources\Lajukasir\PackageResource\Pages;

use App\Filament\Resources\Lajukasir\PackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
