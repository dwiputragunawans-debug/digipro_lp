<?php

namespace App\Filament\Resources\Lajukasir\ScreenshotResource\Pages;

use App\Filament\Resources\Lajukasir\ScreenshotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScreenshots extends ListRecords
{
    protected static string $resource = ScreenshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
