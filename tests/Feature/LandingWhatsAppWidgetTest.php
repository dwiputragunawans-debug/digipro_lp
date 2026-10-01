<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\Hero;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LandingWhatsAppWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The settings row is remembered for the duration of a request, and a
        // single PHP process runs every test, so start each test from a clean slate.
        SiteSetting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();

        parent::tearDown();
    }

    public function test_the_whatsapp_button_is_rendered_with_the_default_number(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-whatsapp-widget', $html);
        $this->assertStringContainsString($this->defaultUrl(), $html);
        $this->assertStringContainsString(SiteSetting::DEFAULT_WHATSAPP_GREETING, $html);
        $this->assertStringContainsString('Mulai Konsultasi', $html);

        // Kartu obrolannya masih tertutup selama tombolnya belum ditekan.
        $this->assertStringContainsString('aria-expanded="false"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_the_whatsapp_button_uses_the_number_and_messages_from_the_settings(): void
    {
        SiteSetting::create([
            'site_name' => 'DigiPro',
            'whatsapp_number' => '0896-1234-5678',
            'whatsapp_greeting' => 'Halo, ada yang bisa kami bantu?',
            'whatsapp_message' => 'Saya mau dibuatkan website toko online.',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        // Nomor yang ditulis dengan awalan 0 dirapikan jadi format internasional,
        // supaya tautan wa.me-nya tetap bisa dibuka.
        $url = 'https://wa.me/6289612345678?text='.rawurlencode('Saya mau dibuatkan website toko online.');

        $this->assertStringContainsString($url, $html);
        $this->assertStringContainsString('Halo, ada yang bisa kami bantu?', $html);

        // Sekali di tombol "Hubungi Kami" di header, sekali di menu ponsel dan
        // tablet, sekali lagi di tombol kartu obrolan.
        $this->assertSame(3, substr_count($html, $url));
    }

    public function test_every_whatsapp_link_opens_in_a_new_tab(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Tiga tautan WhatsApp di halaman DigiPro Solution: tombol di menu
        // mendatar, tombol di menu ponsel dan tablet, serta tombol di kartu
        // obrolan mengambang.
        $this->assertSame(3, substr_count($html, 'data-whatsapp-click'));
        $this->assertSame(3, substr_count($html, 'target="_blank"'));

        // Tautan yang diisi admin di banner dan ajakan bertindak pun ikut dibuka
        // di tab baru begitu isinya tautan WhatsApp.
        $this->assertStringContainsString('target="_blank" rel="noopener"', $html);
    }

    public function test_the_floating_whatsapp_button_can_be_turned_off_from_the_admin_form(): void
    {
        SiteSetting::create([
            'site_name' => 'DigiPro',
            'whatsapp_number' => '0896-1234-5678',
            'whatsapp_enabled' => false,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('data-whatsapp-widget', $html);
        $this->assertStringNotContainsString('Mulai Konsultasi', $html);

        // Tombol "Hubungi Kami" di header bukan bagian dari tombol mengambang,
        // jadi tautannya tetap ada.
        $this->assertStringContainsString('Hubungi Kami', $html);
        $this->assertStringContainsString('https://wa.me/6289612345678?text=', $html);
    }

    public function test_a_whatsapp_link_from_the_admin_without_a_message_gets_the_site_message(): void
    {
        Hero::create([
            'title' => 'Judul banner',
            // Tautan ini disalin dari tombol "bagikan ke WhatsApp", jadi kolom
            // pesannya masih kosong.
            'cta_primary_url' => 'https://api.whatsapp.com/send/?phone=6289662135974&text&type=phone_number&app_absent=0',
            'cta_primary_text' => 'Konsultasi Gratis',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        // Nomornya tetap dari tautan admin, tetapi tautannya ditulis ulang
        // menjadi bentuk wa.me supaya pesannya ikut terisi, juga saat WhatsApp
        // membuka aplikasinya.
        $this->assertStringContainsString(
            'https://wa.me/6289662135974?text='.rawurlencode(SiteSetting::DEFAULT_WHATSAPP_MESSAGE),
            $html,
        );
        $this->assertStringNotContainsString('app_absent', $html);
    }

    public function test_a_whatsapp_link_that_already_has_a_message_keeps_that_message(): void
    {
        $url = 'https://wa.me/628111222333?text='.rawurlencode('Halo, saya mau tanya harga paket.');

        Hero::create([
            'title' => 'Judul banner',
            'cta_primary_url' => $url,
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString($url, $html);
    }

    public function test_a_whatsapp_share_link_that_already_has_a_message_is_tidied_up(): void
    {
        // Tautan "bagikan ke WhatsApp" yang pesannya sudah diisi admin: pesannya
        // dipertahankan, tetapi bentuknya dibersihkan supaya aplikasi WhatsApp
        // tidak melewatinya.
        Hero::create([
            'title' => 'Judul banner',
            'cta_primary_url' => 'https://api.whatsapp.com/send/?phone=6281234567890&text=Halo%20kak&type=phone_number&app_absent=0',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('https://wa.me/6281234567890?text=Halo%20kak', $html);
        $this->assertStringNotContainsString('api.whatsapp.com', $html);
    }

    public function test_a_hero_link_that_is_not_whatsapp_is_left_alone(): void
    {
        Hero::create([
            'title' => 'Judul banner',
            'cta_primary_url' => 'https://example.test/konsultasi',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="https://example.test/konsultasi"', $html);
    }

    public function test_the_back_to_top_button_is_rendered_hidden(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-back-to-top', $html);
        // Class "flex" baru dipasang oleh JavaScript saat halaman sudah digulir,
        // jadi tombolnya diam di tempat selama belum dibutuhkan.
        $this->assertStringContainsString('Kembali ke atas halaman', $html);
    }

    public function test_the_number_inside_a_whatsapp_link_is_read_back_and_tidied_up(): void
    {
        $setting = new SiteSetting(['whatsapp_number' => '0812-0000-1111']);

        // Tautan wa.me menyimpan nomornya di jalur, tautan "bagikan ke
        // WhatsApp" di parameter phone. Keduanya harus terbaca.
        $this->assertSame('628111222333', $setting->whatsappPhoneFromUrl('https://wa.me/628111222333'));
        $this->assertSame('628111222333', $setting->whatsappPhoneFromUrl('https://web.whatsapp.com/send?phone=08111222333'));
        $this->assertSame('6281234567890', $setting->whatsappPhoneFromUrl('https://api.whatsapp.com/send/?phone=6281234567890&text&type=phone_number&app_absent=0'));

        // Tautan yang bukan WhatsApp, tautan wa.me tanpa nomor, dan nomor yang
        // tidak masuk akal tidak menghasilkan nomor apa pun.
        $this->assertNull($setting->whatsappPhoneFromUrl('https://example.test/kontak'));
        $this->assertNull($setting->whatsappPhoneFromUrl('https://wa.me/message/ABCDEF'));
        $this->assertNull($setting->whatsappPhoneFromUrl(null));

        // Tanpa nomor yang bisa dibaca, alamat milik admin tetap dipakai supaya
        // obrolannya tidak berpindah ke nomor lain.
        $this->assertSame(
            'https://wa.me/message/ABCDEF?text=Halo',
            $setting->whatsappUrlWithText('https://wa.me/message/ABCDEF', 'Halo'),
        );

        // Tautan yang bukan WhatsApp jatuh ke nomor pengaturan situs.
        $this->assertSame(
            'https://wa.me/6281200001111?text=Halo',
            $setting->whatsappUrlWithText('https://example.test/kontak', 'Halo'),
        );
    }

    public function test_the_whatsapp_number_is_tidied_up_into_the_wa_me_format(): void
    {
        $this->assertSame('6289612345678', (new SiteSetting(['whatsapp_number' => '0896 1234 5678']))->whatsappNumber());
        $this->assertSame('6289612345678', (new SiteSetting(['whatsapp_number' => '+62 896-1234-5678']))->whatsappNumber());
        $this->assertSame('6289612345678', (new SiteSetting(['whatsapp_number' => '6289612345678']))->whatsappNumber());
        $this->assertSame('6289612345678', (new SiteSetting(['whatsapp_number' => '89612345678']))->whatsappNumber());

        // Nomor yang kosong atau tidak masuk akal jatuh ke nomor bawaan supaya
        // tombolnya tidak pernah menuju tautan yang rusak.
        $this->assertSame(SiteSetting::DEFAULT_WHATSAPP_NUMBER, (new SiteSetting)->whatsappNumber());
        $this->assertSame(SiteSetting::DEFAULT_WHATSAPP_NUMBER, (new SiteSetting(['whatsapp_number' => '---']))->whatsappNumber());
    }

    public function test_the_site_setting_page_saves_the_whatsapp_settings(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            ->assertSee('Tombol WhatsApp', false)
            ->fillForm([
                'whatsapp_enabled' => false,
                'whatsapp_number' => '0812-3456-7890',
                'whatsapp_greeting' => 'Halo, butuh website?',
                'whatsapp_message' => 'Saya mau konsultasi website.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_settings', [
            'whatsapp_enabled' => false,
            'whatsapp_number' => '0812-3456-7890',
            'whatsapp_greeting' => 'Halo, butuh website?',
            'whatsapp_message' => 'Saya mau konsultasi website.',
        ]);
    }

    public function test_the_site_setting_page_rejects_a_number_that_is_not_a_phone_number(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            ->fillForm(['whatsapp_number' => 'hubungi saya'])
            ->call('save')
            ->assertHasFormErrors(['whatsapp_number']);
    }

    private function defaultUrl(): string
    {
        return 'https://wa.me/'.SiteSetting::DEFAULT_WHATSAPP_NUMBER.'?text='.rawurlencode(SiteSetting::DEFAULT_WHATSAPP_MESSAGE);
    }
}
