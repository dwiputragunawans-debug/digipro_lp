<?php

namespace App\Models;

use App\Models\Concerns\HasIconName;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu kartu pada bagian "Solusi Digital untuk UMKM" di halaman depan.
 */
class Service extends Model
{
    use HasIconName;

    /**
     * Template pesan WhatsApp tombol kartu ini sebelum admin menulis sendiri.
     *
     * Penanda {paket} diganti dengan judul paketnya, sehingga satu template
     * yang sama tetap menyebut paket yang dipilih pengunjung.
     */
    public const DEFAULT_WHATSAPP_MESSAGE_TEMPLATE = 'Halo DigiPro Solution, saya ingin bertanya tentang paket "{paket}". Apakah bisa dijelaskan lebih lanjut?';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_highlighted' => 'boolean',
        ];
    }

    /**
     * Keunggulan yang ditulis satu per baris di panel admin, siap diulang di Blade.
     *
     * Baris kosong dibuang, dan penanda daftar di awal baris ("-", "*", atau "•")
     * ikut dihilangkan supaya admin bisa menulis dengan gaya apa pun.
     *
     * @return array<int, string>
     */
    protected function benefitList(): Attribute
    {
        return Attribute::get(function (): array {
            $lines = preg_split('/\r\n|\r|\n/', (string) $this->benefits) ?: [];

            return collect($lines)
                ->map(fn (string $line): string => trim(preg_replace('/^[-*•\s]+/u', '', trim($line)) ?? ''))
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * Pesan WhatsApp yang siap dipakai tombol kartu ini: template yang diisi
     * admin, atau template bawaan, dengan {paket} sudah diganti judul paketnya.
     */
    public function whatsappMessage(): string
    {
        $template = filled($this->whatsapp_message)
            ? (string) $this->whatsapp_message
            : self::DEFAULT_WHATSAPP_MESSAGE_TEMPLATE;

        return trim(str_replace('{paket}', (string) $this->title, $template));
    }

    /**
     * Alamat tujuan tombol "Diskusikan Kebutuhan" di kartu ini.
     *
     * 1. Tautan yang diisi admin dipakai apa adanya kalau bukan tautan WhatsApp.
     * 2. Tautan WhatsApp dari admin tetap dipakai nomornya, pesannya diganti
     *    dengan template paket supaya nomor tujuannya tidak berubah.
     * 3. Tanpa tautan dari admin, tombolnya menuju WhatsApp dengan pesan paket.
     *
     * Tanda "#" yang tertulis di kolom tautan dianggap belum diisi: itu tulisan
     * contoh di panel admin, dan tombol yang menunjuk ke "#" hanya melompat ke
     * atas halaman, bukan membuka WhatsApp.
     *
     * Dengan begitu setiap kartu membawa nama paketnya masing-masing tanpa
     * admin perlu menulis satu tautan untuk setiap kartu.
     */
    public function ctaUrlFor(SiteSetting $setting): string
    {
        if (! filled($this->cta_url) || trim((string) $this->cta_url) === '#') {
            return $setting->whatsappUrlFor($this->whatsappMessage());
        }

        if ($setting->isWhatsAppLink($this->cta_url)) {
            return $setting->whatsappUrlWithText($this->cta_url, $this->whatsappMessage());
        }

        return (string) $this->cta_url;
    }
}
