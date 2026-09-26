<?php

namespace App\Filament\Resources\Lajukasir\SettingResource\Pages;

use App\Filament\Resources\Lajukasir\SettingResource;
use App\Filament\Resources\Pages\EditRecord;
use App\Models\Lajukasir\Setting;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected static ?string $title = 'Pengaturan LajuKasir';

    /**
     * Pengaturan LajuKasir adalah satu baris tunggal, jadi route-nya tidak
     * membawa id: barisnya dibuat otomatis saat pertama kali dibuka.
     *
     * Halaman daftar yang dijadikan tujuan setelah menyimpan adalah layar ini
     * sendiri (lihat SettingResource::getPages()), sehingga menyimpan cukup
     * memuat ulang pengaturannya dengan nilai yang baru.
     */
    public function mount(int|string|null $record = null): void
    {
        $this->record = Setting::current();

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }
}
