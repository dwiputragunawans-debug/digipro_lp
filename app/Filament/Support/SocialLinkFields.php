<?php

namespace App\Filament\Support;

use App\Support\Social\Platforms;
use Closure;
use Filament\Forms\Components\TextInput;

/**
 * Kolom alamat media sosial yang dipakai bersama oleh layar Pengaturan Situs
 * (DigiPro Solution) dan Pengaturan LajuKasir.
 *
 * Isinya disusun dari daftar di App\Support\Social\Platforms, jadi halaman
 * pengaturan dan ikon di kaki halaman selalu menampilkan media sosial yang sama
 * dengan urutan yang sama.
 *
 * Tidak ada tombol "tampilkan" yang terpisah: ikon di kaki halaman hanya muncul
 * untuk kolom yang diisi, sehingga mengosongkan kolomnya sudah cukup untuk
 * menyembunyikannya.
 */
class SocialLinkFields
{
    /**
     * Satu kolom isian untuk setiap media sosial.
     *
     * @return array<int, TextInput>
     */
    public static function make(): array
    {
        $fields = [];

        foreach (Platforms::ALL as $key => $platform) {
            $fields[] = TextInput::make(Platforms::column($key))
                ->label($platform['label'])
                ->maxLength(255)
                ->placeholder($platform['hint'])
                ->helperText($platform['note'] ?? null)
                ->rules([
                    // Aturannya sama dengan yang dipakai saat tautannya dirender,
                    // supaya isian yang tidak bisa dipakai ditolak lebih dahulu
                    // dan tidak diam-diam hilang di kaki halaman.
                    fn (): Closure => function (string $attribute, mixed $value, Closure $fail) use ($platform): void {
                        if (filled($value) && Platforms::normalizeUrl($value) === null) {
                            $fail('Alamatnya tidak dikenali. Contoh: '.$platform['hint'].'.');
                        }
                    },
                ])
                ->dehydrateStateUsing(fn (?string $state): ?string => Platforms::normalizeUrl($state));
        }

        return $fields;
    }
}
