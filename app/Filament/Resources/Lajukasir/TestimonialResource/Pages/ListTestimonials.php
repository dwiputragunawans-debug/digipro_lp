<?php

namespace App\Filament\Resources\Lajukasir\TestimonialResource\Pages;

use App\Filament\Resources\Lajukasir\TestimonialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestimonials extends ListRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
