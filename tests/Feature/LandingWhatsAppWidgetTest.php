<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
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

        // Sekali di tombol "Hubungi Kami" di header, sekali di tombol kartu obrolan.
        $this->assertSame(2, substr_count($html, $url));
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

    public function test_the_back_to_top_button_is_rendered_hidden(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('data-back-to-top', $html);
        // Class "flex" baru dipasang oleh JavaScript saat halaman sudah digulir,
        // jadi tombolnya diam di tempat selama belum dibutuhkan.
        $this->assertStringContainsString('Kembali ke atas halaman', $html);
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
