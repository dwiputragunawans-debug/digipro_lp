<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol "Diskusikan Kebutuhan Ini" di kartu bagian "Solusi Digital untuk UMKM".
 *
 * Setiap kartu membawa pesan WhatsApp yang menyebut paketnya sendiri, jadi admin
 * tahu paket mana yang ditanyakan. Tautannya juga dibuka di tab baru supaya
 * pengunjung tidak kehilangan halaman ini saat mulai mengobrol.
 */
class LandingServiceWhatsappTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tautan WhatsApp yang tersimpan di panel admin: nomor sudah ada, tapi
     * kolom pesannya masih kosong.
     */
    private const ADMIN_URL = 'https://api.whatsapp.com/send/?phone=6289662135974&text&type=phone_number&app_absent=0';

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();

        parent::tearDown();
    }

    public function test_each_card_opens_whatsapp_with_a_message_naming_its_own_package(): void
    {
        $this->service(['title' => 'Website Bisnis']);

        $section = $this->solutionSection();

        $this->assertStringContainsString('https://wa.me/'.SiteSetting::DEFAULT_WHATSAPP_NUMBER
            .'?text='.rawurlencode('Halo DigiPro Solution, saya ingin bertanya tentang paket "Website Bisnis". Apakah bisa dijelaskan lebih lanjut?'), $section);

        // Kliknya ikut dihitung di dasbor, dan WhatsApp-nya terbuka di tab baru.
        $this->assertStringContainsString('data-whatsapp-click target="_blank" rel="noopener"', $section);
    }

    public function test_the_two_cards_do_not_share_the_same_message(): void
    {
        $this->service(['title' => 'Website Bisnis', 'order_column' => 0]);
        $this->service(['title' => 'Katalog & Toko Online', 'order_column' => 1]);

        $section = $this->solutionSection();

        $this->assertStringContainsString(rawurlencode('paket "Website Bisnis"'), $section);
        $this->assertStringContainsString(rawurlencode('paket "Katalog & Toko Online"'), $section);
    }

    public function test_the_template_from_the_admin_replaces_the_default_message(): void
    {
        $this->service([
            'title' => 'Aplikasi Web Kustom',
            'whatsapp_message' => 'Halo, saya mau tanya soal paket {paket} dong.',
        ]);

        $section = $this->solutionSection();

        $this->assertStringContainsString(rawurlencode('Halo, saya mau tanya soal paket Aplikasi Web Kustom dong.'), $section);
        // Penandanya sudah diganti, jadi tidak ada {paket} yang bocor ke pesan.
        $this->assertStringNotContainsString(rawurlencode('{paket}'), $section);
    }

    public function test_a_whatsapp_link_from_the_admin_keeps_its_number_and_gets_the_package_message(): void
    {
        $this->service([
            'title' => 'Katalog & Toko Online',
            'cta_url' => self::ADMIN_URL,
        ]);

        $section = $this->solutionSection();

        // Nomor dari tautan admin tetap dipakai, tetapi tautannya dibersihkan
        // menjadi bentuk wa.me: parameter "type=phone_number" dan "app_absent=0"
        // menyuruh WhatsApp menyerahkan obrolannya ke aplikasi, dan di aplikasi
        // pesan yang sudah terisi sering ikut hilang.
        $this->assertStringContainsString(
            'https://wa.me/6289662135974?text='.rawurlencode('Halo DigiPro Solution, saya ingin bertanya tentang paket "Katalog & Toko Online". Apakah bisa dijelaskan lebih lanjut?'),
            $section,
        );
        $this->assertStringNotContainsString('api.whatsapp.com', $section);
        $this->assertStringNotContainsString('app_absent', $section);
    }

    public function test_a_link_that_is_not_whatsapp_is_left_alone(): void
    {
        $this->service([
            'title' => 'Halaman Kontak',
            'cta_url' => 'https://example.test/kontak',
        ]);

        $section = $this->solutionSection();

        $this->assertStringContainsString('https://example.test/kontak', $section);
        $this->assertStringNotContainsString('wa.me', $section);
        $this->assertStringNotContainsString('data-whatsapp-click', $section);
    }

    public function test_the_placeholder_link_is_treated_as_an_empty_field(): void
    {
        // "#" adalah tulisan contoh di kolom tautan panel admin. Tombol yang
        // menunjuk ke sana hanya melompat ke atas halaman, jadi kartunya
        // diarahkan ke WhatsApp seperti kalau kolomnya dikosongkan.
        $this->service([
            'title' => 'Website Profil',
            'cta_url' => '#',
        ]);

        $section = $this->solutionSection();

        $this->assertStringContainsString(
            'https://wa.me/'.SiteSetting::DEFAULT_WHATSAPP_NUMBER.'?text='.rawurlencode(str_replace('{paket}', 'Website Profil', Service::DEFAULT_WHATSAPP_MESSAGE_TEMPLATE)),
            $section,
        );
        $this->assertStringContainsString('data-whatsapp-click target="_blank" rel="noopener"', $section);
    }

    public function test_the_message_falls_back_to_the_default_template(): void
    {
        $service = new Service(['title' => 'Website Profil']);

        $this->assertSame(
            str_replace('{paket}', 'Website Profil', Service::DEFAULT_WHATSAPP_MESSAGE_TEMPLATE),
            $service->whatsappMessage(),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function service(array $attributes): Service
    {
        return Service::create(array_merge([
            'description' => 'Deskripsi paket.',
            'cta_text' => 'Diskusikan Kebutuhan Ini',
            'order_column' => 0,
        ], $attributes));
    }

    /**
     * Markup bagian "Solusi Digital untuk UMKM", supaya asersinya tidak menangkap
     * kata yang sama dari bagian lain di halaman.
     */
    private function solutionSection(): string
    {
        $response = $this->get('/')->assertOk();

        $html = $response->getContent();

        $start = strpos($html, '<section id="solusi"');

        $this->assertNotFalse($start, 'Bagian solusi tidak ada di halaman depan.');

        return substr($html, $start, strpos($html, '</section>', $start) - $start);
    }
}
