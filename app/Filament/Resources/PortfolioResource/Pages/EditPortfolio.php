<?php

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PortfolioResource;
use Filament\Actions;

class EditPortfolio extends EditRecord
{
    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
