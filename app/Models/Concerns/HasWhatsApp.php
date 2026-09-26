<?php

namespace App\Models\Concerns;

/**
 * Tombol WhatsApp yang dipakai bersama oleh pengaturan DigiPro Solution
 * (App\Models\SiteSetting) dan pengaturan halaman LajuKasir
 * (App\Models\Lajukasir\Setting).
 *
 * Kedua situs memakai kartu obrolan yang sama persis (lihat
 * resources/views/components/whatsapp-widget.blade.php), jadi nomor, sapaan,
 * pesan dan tautan wa.me-nya dihitung di satu tempat.
 *
 * Model yang memakai trait ini wajib mendefinisikan konstanta
 * DEFAULT_WHATSAPP_NUMBER, DEFAULT_WHATSAPP_GREETING dan DEFAULT_WHATSAPP_MESSAGE
 * sendiri, supaya masing-masing situs punya sapaannya masing-masing.
 */
trait HasWhatsApp
{
    /**
     * Whether the floating WhatsApp button should be rendered on the landing
     * page. A missing column (for example before migrating) keeps the button
     * visible, because the defaults are always usable.
     */
    public function hasWhatsApp(): bool
    {
        return $this->whatsapp_enabled === null || (bool) $this->whatsapp_enabled;
    }

    /**
     * Nomor WhatsApp in the international format wa.me expects, without the
     * plus sign: 6289662135974. Numbers written as 0896..., +62 896... or
     * 896... are tidied up so the link always opens the right chat.
     */
    public function whatsappNumber(): string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->whatsapp_number) ?? '';

        if ($digits === '') {
            return static::DEFAULT_WHATSAPP_NUMBER;
        }

        return match (true) {
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    /**
     * Sapaan yang tampil di balon obrolan kartu WhatsApp.
     */
    public function whatsappGreeting(): string
    {
        return $this->whatsapp_greeting ?: static::DEFAULT_WHATSAPP_GREETING;
    }

    /**
     * Pesan yang sudah terisi di kolom chat WhatsApp pengunjung.
     */
    public function whatsappMessage(): string
    {
        return $this->whatsapp_message ?: static::DEFAULT_WHATSAPP_MESSAGE;
    }

    /**
     * Tautan wa.me yang dipakai tombol "Hubungi Kami" di header, tombol
     * mengambang dan tombol "Mulai Konsultasi" di kartu obrolan.
     */
    public function whatsappUrl(): string
    {
        return 'https://wa.me/'.$this->whatsappNumber().'?text='.rawurlencode($this->whatsappMessage());
    }

    /**
     * Apakah sebuah tautan menuju WhatsApp.
     *
     * Dipakai supaya tombol WhatsApp yang bukan buatan situs ini, misalnya
     * tombol utama di banner hero yang URL-nya diisi admin, tetap ikut dihitung
     * di dasbor analitik.
     */
    public function isWhatsAppLink(?string $url): bool
    {
        $url = strtolower((string) $url);

        return str_starts_with($url, 'https://wa.me/')
            || str_starts_with($url, 'http://wa.me/')
            || str_starts_with($url, 'https://api.whatsapp.com/');
    }
}
