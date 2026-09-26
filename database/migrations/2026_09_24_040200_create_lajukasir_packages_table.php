<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu kartu paket pada bagian "Paket Harga" halaman LajuKasir.
     *
     * Harga disimpan sebagai teks, bukan angka, karena paket kasir lazim
     * ditulis "Rp 1.500.000" atau "Hubungi Kami" dan tidak pernah dihitung
     * otomatis oleh halaman ini.
     */
    public function up(): void
    {
        Schema::create('lajukasir_packages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name');
            $table->string('price')->nullable();
            $table->string('price_unit')->nullable();
            $table->text('description')->nullable();

            // Pita kecil di sudut kanan atas kartu, misalnya "TERLARIS".
            $table->string('badge')->nullable();

            // Kartu utama digambar dengan latar gradasi gelap: biru tua menuju
            // hijau teal, sama seperti bagian pembuka halamannya.
            $table->boolean('is_highlighted')->default(false);

            // Keunggulan paket, satu per baris.
            $table->text('features')->nullable();

            $table->string('cta_text')->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_packages');
    }
};
