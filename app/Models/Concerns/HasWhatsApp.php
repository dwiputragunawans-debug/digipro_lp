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
     * Tautan wa.me dengan pesan yang berbeda dari pesan bawaan situs.
     *
     * Dipakai tombol di kartu paket: pesan yang sudah terisi di kolom chat
     * WhatsApp menyebut paket yang dipilih pengunjung, bukan pesan umum.
     */
    public function whatsappUrlFor(string $message): string
    {
        return 'https://wa.me/'.$this->whatsappNumber().'?text='.rawurlencode($message);
    }

    /**
     * Tautan WhatsApp yang sudah dibuat admin, tetapi pesannya diganti dengan
     * pesan yang dikirim pemanggil.
     *
     * Nomor tujuannya ikut tautan yang ada, jadi admin tetap yang menentukan
     * mau dikirim ke nomor mana. Kalau tautannya ternyata bukan tautan
     * WhatsApp, yang dipakai adalah nomor di pengaturan situs.
     */
    public function whatsappUrlWithText(?string $url, string $message): string
    {
        if (! $this->isWhatsAppLink($url)) {
            return $this->whatsappUrlFor($message);
        }

        $parts = parse_url((string) $url);

        if ($parts === false || ! filled($parts['host'] ?? null)) {
            return $this->whatsappUrlFor($message);
        }

        // Hanya pesannya yang ditimpa; parameter lain dari tautan admin
        // (nomor telepon, penanda aplikasi) dibiarkan apa adanya.
        parse_str((string) ($parts['query'] ?? ''), $query);

        $query['text'] = $message;

        return ($parts['scheme'] ?? 'https').'://'.$parts['host'].($parts['path'] ?? '')
            .'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Tautan WhatsApp yang pesannya sudah terisi.
     *
     * Tautan yang dibuat admin dipakai apa adanya selama pesannya sudah diisi.
     * Kalau kolom pesannya kosong — seperti tautan yang disalin dari tombol
     * "bagikan ke WhatsApp" — pesan bawaan situs yang dipakai, supaya
     * pengunjung tidak mulai dari kolom chat yang kosong.
     *
     * Tautan yang bukan WhatsApp, dan tautan yang memang belum diisi,
     * dikembalikan tanpa perubahan.
     */
    public function whatsappUrlWithFilledText(?string $url): ?string
    {
        if (! filled($url) || ! $this->isWhatsAppLink($url)) {
            return $url;
        }

        $query = [];

        parse_str((string) parse_url((string) $url, PHP_URL_QUERY), $query);

        if (filled($query['text'] ?? null)) {
            return (string) $url;
        }

        return $this->whatsappUrlWithText($url, $this->whatsappMessage());
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
