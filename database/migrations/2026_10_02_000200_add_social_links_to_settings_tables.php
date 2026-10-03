<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat media sosial yang tampil sebagai ikon di kaki halaman.
 *
 * Kolomnya ditambahkan di kedua tabel pengaturan, karena landing page DigiPro
 * Solution dan halaman LajuKasir punya akunnya sendiri. Isinya boleh kosong:
 * ikon hanya dirender untuk media sosial yang diisi admin, jadi tidak perlu ada
 * penanda tambahan untuk menyembunyikannya.
 */
return new class extends Migration
{
    /**
     * Nama kolomnya sengaja ditulis satu per satu, bukan diambil dari
     * App\Support\Social\Platforms, supaya migrasi ini tetap sama walau daftar
     * media sosialnya kelak berubah.
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'social_instagram',
        'social_threads',
        'social_facebook',
        'social_tiktok',
        'social_youtube',
        'social_x',
        'social_linkedin',
        'social_telegram',
        'social_whatsapp',
        'social_shopee',
    ];

    public function up(): void
    {
        foreach (['site_settings', 'lajukasir_settings'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                foreach (self::COLUMNS as $column) {
                    $table->string($column)->nullable()->after('favicon');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['site_settings', 'lajukasir_settings'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn(self::COLUMNS);
            });
        }
    }
};
