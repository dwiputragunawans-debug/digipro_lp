<?php

namespace App\Filament\Resources\Lajukasir\FaqResource\Pages;

use App\Filament\Resources\Lajukasir\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
