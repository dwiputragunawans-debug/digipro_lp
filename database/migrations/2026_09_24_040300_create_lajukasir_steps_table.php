<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu langkah pada bagian "Cara Mulai" halaman LajuKasir.
     */
    public function up(): void
    {
        Schema::create('lajukasir_steps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('icon')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_steps');
    }
};
