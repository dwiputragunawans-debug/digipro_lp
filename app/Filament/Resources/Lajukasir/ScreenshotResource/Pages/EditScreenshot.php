<?php

namespace App\Filament\Resources\Lajukasir\ScreenshotResource\Pages;

use App\Filament\Resources\Lajukasir\ScreenshotResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditScreenshot extends EditRecord
{
    protected static string $resource = ScreenshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
