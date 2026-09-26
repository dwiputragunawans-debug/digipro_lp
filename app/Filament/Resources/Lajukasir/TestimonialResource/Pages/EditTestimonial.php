<?php

namespace App\Filament\Resources\Lajukasir\TestimonialResource\Pages;

use App\Filament\Resources\Lajukasir\TestimonialResource;
use App\Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditTestimonial extends EditRecord
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
