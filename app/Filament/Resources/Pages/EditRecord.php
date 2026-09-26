<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord as FilamentEditRecord;

/**
 * Base class for the edit pages of this panel.
 *
 * Filament keeps the admin on the same edit page after saving, but the admins
 * of this panel expect to land back on the list of the menu they came from, so
 * every edit page extends this class instead.
 */
abstract class EditRecord extends FilamentEditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
