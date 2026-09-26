<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu kartu pada bagian "Fitur Unggulan" halaman LajuKasir.
     */
    public function up(): void
    {
        Schema::create('lajukasir_features', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Nama ikon Heroicons, lihat App\Support\Heroicons.
            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description')->nullable();

            // Angka lebih kecil tampil lebih dahulu di halaman depan.
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_features');
    }
};
