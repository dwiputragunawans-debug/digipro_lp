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
        return $this->normalizeWhatsAppNumber($this->whatsapp_number) ?: static::DEFAULT_WHATSAPP_NUMBER;
    }

    /**
     * Nomor tujuan yang tersimpan di dalam sebuah tautan WhatsApp.
     *
     * Tautan wa.me menaruh nomornya di jalur (wa.me/62812...), sedangkan tautan
     * "bagikan ke WhatsApp" menaruhnya di parameter phone. Keduanya dirapikan ke
     * bentuk yang sama supaya bisa dipakai lagi di tautan yang baru.
     *
     * Null dikembalikan kalau tautannya bukan tautan WhatsApp, atau kalau di
     * dalamnya memang tidak ada nomor yang bisa dibaca.
     */
    public function whatsappPhoneFromUrl(?string $url): ?string
    {
        if (! $this->isWhatsAppLink($url)) {
            return null;
        }

        $query = [];

        parse_str((string) parse_url((string) $url, PHP_URL_QUERY), $query);

        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        $segment = explode('/', trim($path, '/'))[0] ?? '';

        $phone = $this->normalizeWhatsAppNumber($segment)
            ?: $this->normalizeWhatsAppNumber($query['phone'] ?? null);

        return $phone ?: null;
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
     * Tautan WhatsApp yang sudah dibuat admin, dengan pesan dari pemanggil.
     *
     * Nomor tujuannya tetap diambil dari tautan admin, jadi admin masih yang
     * menentukan obrolannya dikirim ke nomor mana. Bentuk tautannya sendiri
     * selalu ditulis ulang menjadi wa.me yang bersih: parameter bawaan WhatsApp
     * seperti "type=phone_number" dan "app_absent=0" — yang ikut tersalin saat
     * admin memakai tombol "bagikan ke WhatsApp" — dibuang.
     *
     * Alasannya, kedua parameter itu menyuruh WhatsApp menyerahkan obrolannya ke
     * aplikasi, dan di aplikasi pesan yang sudah terisi justru sering hilang.
     * Pesan itu yang memberi tahu admin paket mana yang ditanyakan, jadi ia
     * tidak boleh ikut hilang.
     *
     * Kalau tautannya ternyata bukan tautan WhatsApp, yang dipakai adalah nomor
     * di pengaturan situs.
     */
    public function whatsappUrlWithText(?string $url, string $message): string
    {
        $phone = $this->whatsappPhoneFromUrl($url);

        if ($phone === null) {
            // Tautan WhatsApp yang nomornya tidak terbaca — misalnya tautan
            // pendek wa.me/message/... — dipakai apa adanya supaya obrolannya
            // tidak berpindah ke nomor lain.
            return $this->isWhatsAppLink($url)
                ? $this->whatsappUrlWithReplacedText((string) $url, $message)
                : $this->whatsappUrlFor($message);
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    /**
     * Tautan WhatsApp yang pesannya sudah terisi, dalam bentuk wa.me yang bersih.
     *
     * Pesan yang ditulis admin di tautannya sendiri dipertahankan. Kalau kolom
     * pesannya masih kosong — seperti tautan yang disalin dari tombol "bagikan
     * ke WhatsApp" — pesan bawaan situs yang dipakai, supaya pengunjung tidak
     * mulai dari kolom chat yang kosong.
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

        $text = $query['text'] ?? null;

        return $this->whatsappUrlWithText($url, filled($text) ? (string) $text : $this->whatsappMessage());
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
            || str_starts_with($url, 'https://api.whatsapp.com/')
            || str_starts_with($url, 'https://web.whatsapp.com/');
    }

    /**
     * Nomor yang sudah dirapikan ke bentuk internasional tanpa tanda plus,
     * misalnya 0896..., +62 896... dan 896... menjadi 62896....
     *
     * String kosong dikembalikan kalau tidak ada angka yang bisa dipakai.
     */
    private function normalizeWhatsAppNumber(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?? '';

        return match (true) {
            $digits === '' => '',
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }

    /**
     * Satu tautan WhatsApp dengan pesan yang diganti, tanpa mengubah host atau
     * jalurnya. Hanya dipakai kalau nomornya tidak terbaca dari tautan itu.
     */
    private function whatsappUrlWithReplacedText(string $url, string $message): string
    {
        $parts = parse_url($url) ?: [];
        $query = [];

        parse_str((string) ($parts['query'] ?? ''), $query);

        $query['text'] = $message;

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').($parts['path'] ?? '')
            .'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
