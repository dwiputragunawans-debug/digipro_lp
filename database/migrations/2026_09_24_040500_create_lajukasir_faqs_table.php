<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu tanya jawab pada bagian "Pertanyaan Umum".
     */
    public function up(): void
    {
        Schema::create('lajukasir_faqs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('question');
            $table->text('answer')->nullable();
            $table->unsignedInteger('order_column')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_faqs');
    }
};
