<?php

namespace Database\Seeders;

use App\Models\Lajukasir\Faq;
use App\Models\Lajukasir\Feature;
use App\Models\Lajukasir\Setting;
use App\Models\Lajukasir\Step;
use Illuminate\Database\Seeder;

/**
 * Isi awal halaman LajuKasir.
 *
 * Semuanya boleh diubah atau dihapus dari menu grup "LajuKasir" di panel admin;
 * seeder ini hanya membuat halamannya langsung enak dibaca setelah pemasangan.
 *
 * Isinya sengaja hanya menyebut kemampuan yang benar-benar ada di aplikasi
 * kasirnya, dan tidak memuat harga, tangkapan layar, atau testimoni karangan:
 * ketiga bagian itu baru muncul di halaman setelah admin mengisinya sendiri.
 */
class LajukasirContentSeeder extends Seeder
{
    public function run(): void
    {
        // Satu baris pengaturan; dibuat lewat current() supaya tidak dobel.
        Setting::current();

        if (Feature::query()->doesntExist()) {
            $features = [
                ['heroicon-o-wifi', 'Tetap jalan tanpa internet', 'Penjualan, stok dan laporan disimpan di perangkat toko, jadi kasir tidak berhenti saat koneksi putus.'],
                ['heroicon-o-printer', 'Cetak struk ke printer Bluetooth', 'Sambungkan printer thermal 58 mm atau 80 mm, lalu atur tulisan struknya sesuai nama dan alamat toko.'],
                ['heroicon-o-squares-2x2', 'Stok, pembelian dan opname', 'Catat barang masuk dari supplier, sesuaikan stok, dan telusuri riwayat pergerakan tiap produk.'],
                ['heroicon-o-users', 'Beberapa kasir, beberapa outlet', 'Pisahkan hak akses Owner, Admin dan Kasir, dan kelola lebih dari satu outlet dari satu aplikasi.'],
                ['heroicon-o-banknotes', 'Piutang dan konsinyasi', 'Catat penjualan yang dibayar belakangan, titipan barang dari pemasok, dan retur tanpa buku catatan terpisah.'],
                ['heroicon-o-chart-bar', 'Laporan penjualan harian', 'Lihat penjualan per hari, produk terlaris dan laporan shift, lalu bagikan atau cetak hasilnya sebagai PDF.'],
            ];

            foreach ($features as $index => [$icon, $title, $description]) {
                Feature::create([
                    'icon' => $icon,
                    'title' => $title,
                    'description' => $description,
                    'order_column' => $index,
                ]);
            }
        }

        if (Step::query()->doesntExist()) {
            $steps = [
                ['heroicon-o-chat-bubble-left-right', 'Hubungi kami', 'Ceritakan jenis usaha dan jumlah kasir yang akan memakai aplikasinya.'],
                ['heroicon-o-key', 'Terima lisensi', 'Kami kirim file lisensi yang tinggal diimpor di halaman Aktivasi aplikasi.'],
                ['heroicon-o-building-storefront', 'Isi data toko', 'Buat outlet pertama, akun Owner, dan daftar produk atau impor dari berkas.'],
                ['heroicon-o-rocket-launch', 'Mulai berjualan', 'Kasir siap dipakai; struk bisa langsung dicetak ke printer thermal.'],
            ];

            foreach ($steps as $index => [$icon, $title, $description]) {
                Step::create([
                    'icon' => $icon,
                    'title' => $title,
                    'description' => $description,
                    'order_column' => $index,
                ]);
            }
        }

        if (Faq::query()->doesntExist()) {
            $faqs = [
                ['Apakah aplikasinya butuh internet?', 'Tidak. Semua pencatatan dikerjakan di perangkat toko, sehingga kasir tetap bisa dipakai walau koneksi sedang putus.'],
                ['Perangkat apa saja yang didukung?', 'Aplikasinya berjalan di Android dan di komputer Windows. Aplikasi Android-nya bisa menyambung ke printer thermal lewat Bluetooth.'],
                ['Apakah data penjualan saya aman?', 'Data tersimpan di perangkat Anda sendiri, dan tersedia fitur cadangkan serta pulihkan supaya datanya bisa disimpan di tempat lain.'],
                ['Bisakah dipakai lebih dari satu kasir?', 'Bisa. Setiap pengguna punya akun sendiri dengan hak akses Owner, Admin atau Kasir, dan setiap aktivitas login ikut tercatat.'],
                ['Bagaimana cara memasang dan mengaktifkannya?', 'Impor berkas lisensi yang kami kirim di halaman Aktivasi, lalu isi data toko dan buat akun Owner pertama.'],
                ['Apakah fitur cetak struknya bisa diatur?', 'Bisa. Nama toko, alamat, catatan kaki dan ukuran kertas struk dapat diubah dari menu pengaturan aplikasi.'],
            ];

            foreach ($faqs as $index => [$question, $answer]) {
                Faq::create([
                    'question' => $question,
                    'answer' => $answer,
                    'order_column' => $index,
                ]);
            }
        }
    }
}
