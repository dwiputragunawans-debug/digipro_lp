<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu testimoni pemilik toko pada bagian "Kata Pemilik Toko".
     */
    public function up(): void
    {
        Schema::create('lajukasir_testimonials', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name');
            $table->string('store')->nullable();
            $table->text('quote')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_testimonials');
    }
};
