<?php

namespace App\Filament\Resources\HeroResource\Pages;

use App\Filament\Resources\HeroResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditHero extends EditRecord
{
    protected static string $resource = HeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
