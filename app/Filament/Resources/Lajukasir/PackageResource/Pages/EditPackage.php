<?php

namespace App\Filament\Resources\Lajukasir\PackageResource\Pages;

use App\Filament\Resources\Lajukasir\PackageResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
