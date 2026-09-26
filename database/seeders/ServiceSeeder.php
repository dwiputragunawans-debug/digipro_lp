<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Mengisi menu Layanan dengan tiga paket seperti desain awal halaman depan.
 *
 * Aman dijalankan berulang: kartu yang sudah ada (dicocokkan lewat kolom urutan)
 * tidak ditimpa, jadi perubahan yang sudah dilakukan admin tetap utuh.
 */
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'order_column' => 0,
                'icon' => 'heroicon-o-briefcase',
                'title' => 'Website Profil Bisnis',
                'description' => 'Tampil profesional dengan website profil bisnis untuk meningkatkan kredibilitas.',
                'badge' => null,
                'is_highlighted' => false,
                'benefits' => "Desain Modern & Profesional\nDomain & Hosting Gratis 1 Tahun\nIntegrasi WhatsApp & Media Sosial",
            ],
            [
                'order_column' => 1,
                'icon' => 'heroicon-o-shopping-cart',
                'title' => 'Katalog & Toko Online',
                'description' => 'Tingkatkan penjualan dengan toko online yang mudah dikelola, lengkap dengan pembayaran.',
                'badge' => 'POPULER',
                'is_highlighted' => false,
                'benefits' => "Sistem Keranjang Belanja\nManajemen Produk Mudah\nIntegrasi Metode Pembayaran",
            ],
            [
                'order_column' => 2,
                'icon' => 'heroicon-o-code-bracket',
                'title' => 'Aplikasi Web Custom',
                'description' => 'Sistem khusus yang disesuaikan dengan kebutuhan unik operasional bisnis Anda.',
                'badge' => null,
                'is_highlighted' => true,
                'benefits' => "Sistem Kasir (POS) Berbasis Web\nManajemen Stok/Inventaris\nDashboard Laporan & Analitik",
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['order_column' => $service['order_column']], $service);
        }
    }
}
