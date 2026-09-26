<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;

/**
 * Mengisi menu Proses dengan empat langkah seperti desain awal halaman depan.
 *
 * Ikonnya sengaja dikosongkan supaya yang tampil di halaman depan adalah nomor
 * langkah. Aman dijalankan berulang: langkah yang sudah ada (dicocokkan lewat
 * kolom urutan) tidak ditimpa.
 */
class ProcessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            [
                'order_column' => 0,
                'title' => 'Konsultasi',
                'description' => 'Langkah 1 menuju kesuksesan digital Anda.',
            ],
            [
                'order_column' => 1,
                'title' => 'Pembuatan',
                'description' => 'Langkah 2 menuju kesuksesan digital Anda.',
            ],
            [
                'order_column' => 2,
                'title' => 'Pengujian',
                'description' => 'Langkah 3 menuju kesuksesan digital Anda.',
            ],
            [
                'order_column' => 3,
                'title' => 'Online',
                'description' => 'Langkah 4 menuju kesuksesan digital Anda.',
            ],
        ];

        foreach ($steps as $step) {
            Process::firstOrCreate(['order_column' => $step['order_column']], $step);
        }
    }
}
