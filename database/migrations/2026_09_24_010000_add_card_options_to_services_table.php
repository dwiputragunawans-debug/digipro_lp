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
        Schema::table('services', function (Blueprint $table) {
            $table->string('badge')->nullable()->after('description');
            $table->boolean('is_highlighted')->default(false)->after('badge');
            $table->string('cta_text')->nullable()->after('benefits');
            $table->string('cta_url')->nullable()->after('cta_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['badge', 'is_highlighted', 'cta_text', 'cta_url']);
        });
    }
};
