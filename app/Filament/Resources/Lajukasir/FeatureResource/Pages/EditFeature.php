<?php

namespace App\Filament\Resources\Lajukasir\FeatureResource\Pages;

use App\Filament\Resources\Lajukasir\FeatureResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditFeature extends EditRecord
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
