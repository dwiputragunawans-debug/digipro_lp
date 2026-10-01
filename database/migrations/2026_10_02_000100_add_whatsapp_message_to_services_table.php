<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Template pesan WhatsApp untuk tombol di kartu "Solusi Digital untuk UMKM".
     *
     * Isinya boleh memuat penanda {paket}, yang saat halaman dibuka diganti
     * dengan judul paketnya. Kosong berarti template bawaan di App\Models\Service
     * yang dipakai.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->text('whatsapp_message')->nullable()->after('cta_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('whatsapp_message');
        });
    }
};
