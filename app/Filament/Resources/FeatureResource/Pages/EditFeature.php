<?php

namespace App\Filament\Resources\FeatureResource\Pages;

use App\Filament\Resources\FeatureResource;
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
