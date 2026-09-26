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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            // "page_view" untuk kunjungan halaman, "whatsapp_click" untuk klik
            // tombol WhatsApp. Lihat App\Models\AnalyticsEvent.
            $table->string('type', 24);

            // Halaman yang dibuka, disimpan sebagai path saja (misal "/").
            $table->string('path');

            // Perangkat pengunjung: desktop, tablet atau mobile.
            $table->string('device', 16)->nullable();
            $table->string('browser', 32)->nullable();
            $table->string('platform', 32)->nullable();

            // Asal pengunjung: nama mesin pencari, media sosial atau nama host
            // situs yang mengirim kunjungan. "Langsung" untuk kunjungan tanpa
            // referensi.
            $table->string('source', 64)->nullable();

            // Tanggal kunjungan menurut zona waktu dasbor, bukan menurut UTC.
            // Angka harian di dasbor dihitung dari kolom ini supaya harinya
            // berganti di tengah malam waktu Indonesia.
            $table->date('visited_on');

            // Hash dari id acak di cookie pengunjung. Alamat IP tidak pernah
            // disimpan, dan hash ini hanya dipakai untuk menghitung pengunjung
            // unik per hari.
            $table->string('visitor', 64);

            $table->timestamps();

            // Hampir semua pertanyaan di dasbor dikelompokkan per jenis kejadian
            // dan per tanggal.
            $table->index(['type', 'visited_on']);
            $table->index(['type', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
