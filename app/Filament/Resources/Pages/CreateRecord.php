<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord as FilamentCreateRecord;

/**
 * Base class for the create pages of this panel.
 *
 * Filament opens the edit page of the record that was just created, but the
 * admins of this panel expect to land back on the list of the menu they came
 * from, so every create page extends this class instead.
 */
abstract class CreateRecord extends FilamentCreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
