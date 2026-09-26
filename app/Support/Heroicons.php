<?php

namespace App\Support;

use Illuminate\Support\Str;
use Throwable;

/**
 * Helper untuk ikon Heroicons yang dipakai di kartu fitur landing page.
 *
 * Yang disimpan di database adalah nama komponen Blade dari paket
 * blade-ui-kit/blade-heroicons, misalnya "heroicon-o-bolt". Daftar di options()
 * sengaja dibatasi supaya admin hanya bisa memilih ikon yang benar-benar ada.
 */
class Heroicons
{
    /**
     * Ikon yang dipakai kalau kolomnya kosong atau namanya tidak dikenal.
     */
    public const FALLBACK = 'heroicon-o-bolt';

    /**
     * Ikon yang cocok untuk kartu fitur, dengan label bahasa Indonesia.
     *
     * @param  string|null  $current  Nilai yang tersimpan sekarang, supaya nilai
     *                                lama yang tidak ada di daftar tidak hilang.
     * @return array<string, string>
     */
    public static function options(?string $current = null): array
    {
        $options = [
            'heroicon-o-bolt' => 'Petir, cepat (bolt)',
            'heroicon-o-rocket-launch' => 'Roket, cepat (rocket-launch)',
            'heroicon-o-sparkles' => 'Kerlip, fitur (sparkles)',
            'heroicon-o-squares-2x2' => 'Kotak-kotak, fitur (squares-2x2)',
            'heroicon-o-device-phone-mobile' => 'Ponsel, mobile friendly (device-phone-mobile)',
            'heroicon-o-device-tablet' => 'Tablet (device-tablet)',
            'heroicon-o-computer-desktop' => 'Desktop (computer-desktop)',
            'heroicon-o-magnifying-glass' => 'Kaca pembesar, SEO (magnifying-glass)',
            'heroicon-o-globe-alt' => 'Globe, website (globe-alt)',
            'heroicon-o-globe-asia-australia' => 'Globe, jangkauan luas (globe-asia-australia)',
            'heroicon-o-currency-dollar' => 'Harga, dolar (currency-dollar)',
            'heroicon-o-banknotes' => 'Harga, uang (banknotes)',
            'heroicon-o-credit-card' => 'Pembayaran, kartu (credit-card)',
            'heroicon-o-calculator' => 'Kalkulator, hitung (calculator)',
            'heroicon-o-receipt-percent' => 'Struk, pajak (receipt-percent)',
            'heroicon-o-chart-bar' => 'Laporan, grafik batang (chart-bar)',
            'heroicon-o-chart-pie' => 'Laporan, diagram (chart-pie)',
            'heroicon-o-presentation-chart-line' => 'Presentasi, tren (presentation-chart-line)',
            'heroicon-o-arrow-trending-up' => 'Tren naik (arrow-trending-up)',
            'heroicon-o-shield-check' => 'Perisai, aman (shield-check)',
            'heroicon-o-lock-closed' => 'Gembok, aman (lock-closed)',
            'heroicon-o-key' => 'Kunci (key)',
            'heroicon-o-check-badge' => 'Badge centang, terjamin (check-badge)',
            'heroicon-o-clipboard-document-check' => 'Ceklis dokumen (clipboard-document-check)',
            'heroicon-o-lifebuoy' => 'Pelampung, dukungan (lifebuoy)',
            'heroicon-o-chat-bubble-left-right' => 'Obrolan, dukungan (chat-bubble-left-right)',
            'heroicon-o-phone' => 'Telepon (phone)',
            'heroicon-o-envelope' => 'Surel (envelope)',
            'heroicon-o-clock' => 'Jam, tepat waktu (clock)',
            'heroicon-o-calendar-days' => 'Kalender, jadwal (calendar-days)',
            'heroicon-o-star' => 'Bintang, unggulan (star)',
            'heroicon-o-heart' => 'Hati, suka (heart)',
            'heroicon-o-hand-thumb-up' => 'Jempol, direkomendasikan (hand-thumb-up)',
            'heroicon-o-trophy' => 'Trofi, prestasi (trophy)',
            'heroicon-o-fire' => 'Api, populer (fire)',
            'heroicon-o-light-bulb' => 'Lampu, ide (light-bulb)',
            'heroicon-o-paint-brush' => 'Kuas, desain (paint-brush)',
            'heroicon-o-code-bracket' => 'Kode, teknis (code-bracket)',
            'heroicon-o-cog-6-tooth' => 'Roda gigi, pengaturan (cog-6-tooth)',
            'heroicon-o-wrench-screwdriver' => 'Perkakas, layanan (wrench-screwdriver)',
            'heroicon-o-adjustments-horizontal' => 'Pengaturan (adjustments-horizontal)',
            'heroicon-o-cube' => 'Kubus, produk (cube)',
            'heroicon-o-circle-stack' => 'Tumpukan, stok (circle-stack)',
            'heroicon-o-shopping-cart' => 'Keranjang belanja (shopping-cart)',
            'heroicon-o-shopping-bag' => 'Tas belanja (shopping-bag)',
            'heroicon-o-building-storefront' => 'Toko (building-storefront)',
            'heroicon-o-truck' => 'Truk, pengiriman (truck)',
            'heroicon-o-qr-code' => 'Kode QR (qr-code)',
            'heroicon-o-printer' => 'Printer (printer)',
            'heroicon-o-wifi' => 'Wifi, koneksi (wifi)',
            'heroicon-o-cloud-arrow-up' => 'Awan, hosting (cloud-arrow-up)',
            'heroicon-o-server' => 'Server (server)',
            'heroicon-o-inbox' => 'Kotak masuk (inbox)',
            'heroicon-o-document-text' => 'Dokumen (document-text)',
            'heroicon-o-photo' => 'Foto (photo)',
            'heroicon-o-video-camera' => 'Kamera video (video-camera)',
            'heroicon-o-speaker-wave' => 'Pengeras suara, promosi (speaker-wave)',
            'heroicon-o-users' => 'Pengguna (users)',
            'heroicon-o-user-group' => 'Grup pengguna (user-group)',
            'heroicon-o-academic-cap' => 'Topi wisuda, edukasi (academic-cap)',
            'heroicon-o-briefcase' => 'Tas kerja (briefcase)',
            'heroicon-o-map-pin' => 'Pin lokasi (map-pin)',
        ];

        // Nilai lama yang belum ada di daftar tetap ditawarkan, supaya membuka
        // lalu menyimpan form tidak langsung menimpa ikon yang sudah dipakai.
        if (filled($current) && ! array_key_exists($current, $options)) {
            $options[$current] = $current.' (tidak ada di daftar, tampil sebagai '.static::normalize($current).')';
        }

        return $options;
    }

    /**
     * Pilihan untuk panel admin: ikonnya ikut digambar di dalam tiap pilihan.
     *
     * @return array<string, string>
     */
    public static function optionsWithIcons(?string $current = null): array
    {
        $options = [];

        foreach (static::options($current) as $icon => $label) {
            $options[$icon] = static::label($icon, $label);
        }

        return $options;
    }

    /**
     * Label HTML berisi ikon dan namanya, untuk dipakai di pilihan Select.
     */
    public static function label(string $icon, string $label): string
    {
        return '<span class="flex items-center gap-2">'
            .svg(static::normalize($icon), 'w-5 h-5 shrink-0')->toHtml()
            .'<span>'.e($label).'</span></span>';
    }

    /**
     * Nama ikon yang dikenali dari nilai yang tersimpan, atau null kalau kolomnya
     * kosong atau isinya tidak dikenal.
     *
     * Dipakai kalau ikon memang pilihan: pada langkah di bagian "Proses", nomor
     * langkah lebih masuk akal daripada ikon cadangan.
     */
    public static function resolve(?string $icon): ?string
    {
        foreach (static::candidates(trim((string) $icon)) as $candidate) {
            if (static::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Ubah nilai apa pun yang ada di database menjadi nama ikon yang valid.
     *
     * Menerima "bolt", "o-bolt", "heroicon-o-bolt", "s-bolt", dan nama gaya lama
     * seperti "AcademicCapIcon" atau "globe-alt". Nilai yang kosong atau tidak
     * dikenal dikembalikan sebagai ikon cadangan.
     */
    public static function normalize(?string $icon): string
    {
        return static::resolve($icon) ?? static::FALLBACK;
    }

    /**
     * Nama ikon yang mungkin dimaksud, diurutkan dari yang paling mungkin.
     *
     * @return list<string>
     */
    private static function candidates(string $icon): array
    {
        if ($icon === '') {
            return [];
        }

        $candidates = str_starts_with($icon, 'heroicon-')
            ? [$icon]
            : ['heroicon-'.(preg_match('/^[osmc]-/', $icon) ? $icon : 'o-'.$icon)];

        // Nama ikon gaya lama, misalnya "AcademicCapIcon" atau "AcademicCap".
        $legacy = Str::kebab((string) preg_replace('/Icon$/', '', $icon));

        if ($legacy !== '') {
            $candidates[] = 'heroicon-o-'.$legacy;
        }

        return $candidates;
    }

    /**
     * Cek apakah ikon tersedia di paket blade-ui-kit/blade-heroicons.
     */
    public static function exists(string $icon): bool
    {
        try {
            svg($icon);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
