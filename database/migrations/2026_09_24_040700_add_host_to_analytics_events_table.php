<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Halaman mana yang menghasilkan sebuah kejadian analitik.
     *
     * Sebelum kolom ini ada, hanya ada satu landing page sehingga path saja
     * sudah cukup untuk mengenali halamannya. Setelah halaman LajuKasir ikut
     * dilayani dari subdomainnya sendiri, dua halaman berbeda sama-sama
     * memakai path "/", jadi angka keduanya harus bisa dipisahkan.
     *
     * Baris lama dibiarkan kosong dan tetap dihitung sebagai milik DigiPro
     * Solution, lihat AnalyticsEvent::scopeForHost().
     */
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('host', 128)->nullable()->after('path');

            // Pertanyaan di dasbor selalu dibatasi ke satu situs lebih dahulu.
            $table->index(['host', 'type', 'visited_on']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropIndex(['host', 'type', 'visited_on']);
            $table->dropColumn('host');
        });
    }
};
