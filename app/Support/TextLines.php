<?php

namespace App\Support;

/**
 * Pembantu untuk kolom teks yang isinya beberapa baris, seperti daftar
 * keunggulan paket atau daftar angka di bagian pembuka.
 *
 * Admin menulisnya satu per baris di panel admin, jadi baris kosong dibuang dan
 * penanda daftar di awal baris ("-", "*", atau "•") ikut dihilangkan supaya
 * admin bisa menulis dengan gaya apa pun.
 */
class TextLines
{
    /**
     * @return array<int, string>
     */
    public static function split(?string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text) ?: [];

        return collect($lines)
            ->map(fn (string $line): string => trim(preg_replace('/^[-*•\s]+/u', '', trim($line)) ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Baris yang ditulis "5.000|Toko aktif" dipecah menjadi pasangan
     * angka dan keterangannya. Baris tanpa pemisah tetap ditampilkan dengan
     * keterangan kosong supaya tidak ada tulisan admin yang hilang.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function pairs(?string $text, string $separator = '|'): array
    {
        return array_map(function (string $line) use ($separator): array {
            $parts = array_map('trim', explode($separator, $line, 2));

            return [
                'value' => $parts[0],
                'label' => $parts[1] ?? '',
            ];
        }, self::split($text));
    }
}
