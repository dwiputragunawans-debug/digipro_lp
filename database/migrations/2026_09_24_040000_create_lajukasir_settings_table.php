<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengaturan halaman LajuKasir.
     *
     * Isinya sengaja dipisah dari tabel site_settings milik DigiPro Solution
     * supaya kedua situs bisa diisi tanpa saling menimpa, walau keduanya
     * memakai komponen WhatsApp, logo dan tag SEO yang sama.
     */
    public function up(): void
    {
        Schema::create('lajukasir_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Merek
            $table->string('site_name')->nullable();
            $table->string('site_url')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('logo_adaptive')->default(true);
            $table->string('brand_tagline')->nullable();

            // Tombol WhatsApp (dipakai kartu obrolan yang sama dengan DigiPro)
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_greeting')->nullable();
            $table->string('whatsapp_message')->nullable();
            $table->boolean('whatsapp_enabled')->default(true);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('robots_index')->default(true);

            // Bagian pembuka
            $table->string('hero_badge')->nullable();
            $table->text('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_primary_text')->nullable();
            $table->string('hero_primary_url')->nullable();
            $table->string('hero_secondary_text')->nullable();
            $table->string('hero_secondary_url')->nullable();
            $table->text('hero_stats')->nullable();
            $table->string('hero_note')->nullable();

            // Judul dan subjudul tiap bagian
            $table->string('features_heading')->nullable();
            $table->string('features_subheading')->nullable();
            $table->string('packages_heading')->nullable();
            $table->string('packages_subheading')->nullable();
            $table->string('gallery_heading')->nullable();
            $table->string('gallery_subheading')->nullable();
            $table->string('steps_heading')->nullable();
            $table->string('steps_subheading')->nullable();
            $table->string('faq_heading')->nullable();
            $table->string('faq_subheading')->nullable();
            $table->string('testimonials_heading')->nullable();
            $table->string('testimonials_subheading')->nullable();

            // Bagian unduhan aplikasi
            $table->string('download_heading')->nullable();
            $table->string('download_subheading')->nullable();
            $table->string('download_android_label')->nullable();
            $table->string('download_android_url')->nullable();
            $table->string('download_windows_label')->nullable();
            $table->string('download_windows_url')->nullable();
            $table->string('download_note')->nullable();

            // Ajakan bertindak penutup
            $table->string('cta_title')->nullable();
            $table->text('cta_description')->nullable();
            $table->string('cta_button_text')->nullable();
            $table->string('cta_button_url')->nullable();
            $table->string('cta_disclaimer')->nullable();

            // Kaki halaman
            $table->text('footer_description')->nullable();
            $table->text('footer_address')->nullable();
            $table->string('footer_hours')->nullable();
            $table->string('footer_copyright')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lajukasir_settings');
    }
};
