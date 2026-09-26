<?php

namespace App\Filament\Resources\SiteSettingResource\Pages;

use App\Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\SiteSettingResource;
use App\Models\SiteSetting;

class EditSiteSetting extends EditRecord
{
    protected static string $resource = SiteSettingResource::class;

    protected static ?string $title = 'Pengaturan Situs';

    /**
     * Site settings are a singleton, so the route carries no record id: the one
     * and only settings row is created on demand and edited here. The list page
     * this page redirects to after saving is this very screen, so saving simply
     * reloads the settings with the new values in place.
     */
    public function mount(int|string|null $record = null): void
    {
        $this->record = SiteSetting::current();

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }
}
