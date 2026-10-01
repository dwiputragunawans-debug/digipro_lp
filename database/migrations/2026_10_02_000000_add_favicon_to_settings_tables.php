<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Favicon: ikon kecil yang tampil di tab browser, riwayat dan bookmark.
     *
     * Kolomnya ditambahkan ke kedua tabel pengaturan karena logo, tag SEO dan
     * favicon-nya dipakai bersama lewat trait HasSiteBranding: setiap situs
     * boleh punya ikonnya sendiri tanpa saling menimpa.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('favicon')->nullable()->after('logo_adaptive');
        });

        Schema::table('lajukasir_settings', function (Blueprint $table) {
            $table->string('favicon')->nullable()->after('logo_adaptive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('favicon');
        });

        Schema::table('lajukasir_settings', function (Blueprint $table) {
            $table->dropColumn('favicon');
        });
    }
};
