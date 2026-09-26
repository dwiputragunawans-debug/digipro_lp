<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu tangkapan layar aplikasi pada bagian "Tampilan Aplikasi".
     */
    public function up(): void
    {
        Schema::create('lajukasir_screenshots', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_screenshots');
    }
};
