<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('logo_adaptive');
            $table->string('whatsapp_greeting')->nullable()->after('whatsapp_number');
            $table->string('whatsapp_message')->nullable()->after('whatsapp_greeting');
            $table->boolean('whatsapp_enabled')->default(true)->after('whatsapp_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'whatsapp_greeting', 'whatsapp_message', 'whatsapp_enabled']);
        });
    }
};
