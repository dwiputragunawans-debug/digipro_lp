<?php

namespace App\Support\Analytics;

/**
 * Warna untuk diagram di dasbor.
 *
 * Chart.js hanya memakai satu warna bawaan dari tema Filament, sehingga setiap
 * potongan diagram akan terlihat sama. Palet ini memberi tiap potongan dan tiap
 * garis warnanya sendiri supaya perbedaannya terlihat.
 */
class ChartPalette
{
    /**
     * @var list<string>
     */
    public const COLORS = [
        '#2563eb', // biru
        '#25d366', // hijau WhatsApp
        '#f59e0b', // kuning
        '#8b5cf6', // ungu
        '#0ea5e9', // biru langit
        '#ef4444', // merah
        '#14b8a6', // tosca
        '#f97316', // jingga
        '#6366f1', // indigo
        '#ec4899', // merah muda
    ];

    /**
     * Sejumlah warna, diulang dari awal kalau potongannya lebih banyak daripada
     * warna yang tersedia.
     *
     * @return list<string>
     */
    public static function colors(int $count): array
    {
        $colors = [];

        for ($index = 0; $index < $count; $index++) {
            $colors[] = self::COLORS[$index % count(self::COLORS)];
        }

        return $colors;
    }
}
