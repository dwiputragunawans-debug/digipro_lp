<?php

namespace Tests\Feature;

use App\Filament\Resources\Lajukasir\SettingResource\Pages\EditSetting;
use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Social\Platforms;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Ikon media sosial di kaki halaman.
 *
 * Kedua situs punya akunnya sendiri, jadi alamatnya disimpan terpisah di
 * site_settings (DigiPro Solution) dan lajukasir_settings (LajuKasir). Yang
 * tampil hanya media sosial yang diisi admin, sehingga tidak ada ikon yang
 * menuju halaman kosong.
 */
class SocialLinksTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'lajukasir.digipro-solution.com';

    protected function setUp(): void
    {
        parent::setUp();

        // Baris pengaturan diingat selama satu permintaan, sedangkan satu proses
        // PHP menjalankan seluruh pengujian.
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    public function test_the_footer_has_no_social_icon_before_anything_is_filled_in(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        // Judul dan ikonnya sama-sama tidak dirender selama kolomnya kosong.
        // Kata "Media Sosial" sendiri tetap ada di bagian lain halaman ini, jadi
        // yang diperiksa adalah judul kolomnya dan ikonnya.
        $this->assertStringNotContainsString('>Media Sosial</h4>', $html);
        $this->assertStringNotContainsString('aria-label="Instagram"', $html);
    }

    public function test_only_the_social_media_that_is_filled_in_is_rendered(): void
    {
        SiteSetting::current()->update([
            'social_instagram' => 'https://instagram.com/digiprosolution',
            'social_facebook' => 'facebook.com/digiprosolution',
        ]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Media Sosial', $html);
        $this->assertStringContainsString('aria-label="Instagram"', $html);
        $this->assertStringContainsString('href="https://instagram.com/digiprosolution"', $html);

        // Alamat yang ditulis tanpa "https://" tetap bisa dipakai.
        $this->assertStringContainsString('aria-label="Facebook"', $html);
        $this->assertStringContainsString('href="https://facebook.com/digiprosolution"', $html);

        // Dibuka di tab baru supaya pengunjung tidak meninggalkan halaman ini.
        $this->assertMatchesRegularExpression('/<a\b[^>]*target="_blank"[^>]*rel="noopener noreferrer"/', $html);

        // Media sosial yang dikosongkan tidak ikut dirender.
        $this->assertStringNotContainsString('aria-label="Shopee"', $html);
        $this->assertStringNotContainsString('aria-label="TikTok"', $html);
    }

    public function test_a_whatsapp_number_becomes_a_wa_me_link_counted_in_the_analytics(): void
    {
        SiteSetting::current()->update(['social_whatsapp' => '0812 3456 7890']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="https://wa.me/6281234567890"', $html);

        // Penandanya sama dengan tombol dan kartu obrolan WhatsApp, jadi kliknya
        // ikut terhitung di menu Analitik.
        $this->assertMatchesRegularExpression('/<a\b[^>]*data-whatsapp-click[^>]*aria-label="WhatsApp"/', $html);
    }

    public function test_the_lajukasir_page_renders_its_own_social_links(): void
    {
        Setting::current()->update(['social_instagram' => 'https://instagram.com/lajukasir']);
        SiteSetting::current()->update(['social_instagram' => 'https://instagram.com/digiprosolution']);
        Setting::forgetCurrent();
        SiteSetting::forgetCurrent();

        $html = $this->get('http://'.self::HOST.'/')->assertOk()->getContent();

        $this->assertStringContainsString('href="https://instagram.com/lajukasir"', $html);
        // Akun DigiPro Solution tidak ikut bocor ke halaman LajuKasir.
        $this->assertStringNotContainsString('instagram.com/digiprosolution', $html);
    }

    public function test_addresses_without_a_scheme_are_completed_and_unsafe_ones_are_dropped(): void
    {
        $this->assertSame('https://instagram.com/namaakun', Platforms::normalizeUrl('instagram.com/namaakun'));
        $this->assertSame('https://instagram.com/namaakun', Platforms::normalizeUrl('  //instagram.com/namaakun  '));
        $this->assertSame('https://www.instagram.com/namaakun/', Platforms::normalizeUrl('https://www.instagram.com/namaakun/'));
        $this->assertSame('https://wa.me/628123456789', Platforms::normalizeUrl('+62 812-3456-789'));

        // Skema yang tidak dikenal tidak pernah sampai ke dalam atribut href.
        $this->assertNull(Platforms::normalizeUrl('javascript:alert(1)'));
        $this->assertNull(Platforms::normalizeUrl('   '));
        $this->assertNull(Platforms::normalizeUrl(null));

        $this->assertFalse((new SiteSetting)->hasSocialLinks());
        $this->assertFalse((new SiteSetting(['social_instagram' => 'javascript:alert(1)']))->hasSocialLinks());
    }

    public function test_every_social_media_is_rendered_with_its_own_icon_and_address(): void
    {
        // Semua kolom diisi sekaligus, memakai contoh alamat yang tertulis di
        // panel admin, supaya setiap media sosial yang tersedia teruji.
        $values = [];

        foreach (Platforms::ALL as $key => $platform) {
            $values[Platforms::column($key)] = $platform['hint'];
        }

        SiteSetting::current()->update($values);

        $html = $this->get('/')->assertOk()->getContent();

        foreach (Platforms::ALL as $key => $platform) {
            $url = Platforms::normalizeUrl($platform['hint']);

            $this->assertSame(1, substr_count($html, 'aria-label="'.$platform['label'].'"'), $key);
            $this->assertStringContainsString('href="'.$url.'"', $html);

            // Gambar ikonnya tersimpan sebagai gambar SVG sebaris, bukan berkas
            // terpisah, sehingga tidak ada permintaan tambahan dari browser.
            $this->assertGreaterThan(50, strlen($platform['icon']), $key);
            $this->assertStringContainsString($platform['icon'], $html, $key);
        }
    }

    public function test_the_settings_tables_have_a_column_for_every_social_media(): void
    {
        // Daftar media sosial dan kolom di database harus tetap sejalan, supaya
        // isian admin tidak diam-diam tidak tersimpan.
        foreach (['site_settings', 'lajukasir_settings'] as $table) {
            $this->assertSame(
                Platforms::columns(),
                array_values(array_intersect(Schema::getColumnListing($table), Platforms::columns())),
                $table,
            );
        }
    }

    public function test_the_settings_screen_saves_the_digipro_social_links(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            ->assertSee('Media Sosial', false)
            ->fillForm(['social_instagram' => 'instagram.com/digiprosolution'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Alamatnya disimpan dalam bentuk yang siap dipasang sebagai tautan.
        $this->assertSame('https://instagram.com/digiprosolution', SiteSetting::current()->social_instagram);
    }

    public function test_the_lajukasir_settings_screen_saves_its_social_links(): void
    {
        // Panelnya hanya dilayani di lingkungan lokal, lihat
        // Filament\Http\Middleware\Authenticate.
        config(['app.env' => 'local']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());

        Livewire::test(EditSetting::class)
            ->fillForm(['social_shopee' => 'shopee.co.id/lajukasir'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://shopee.co.id/lajukasir', Setting::current()->social_shopee);
    }

    public function test_an_unusable_address_is_refused_by_the_settings_screen(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            ->fillForm(['social_facebook' => 'javascript:alert(1)'])
            ->call('save')
            ->assertHasFormErrors(['social_facebook']);

        $this->assertNull(SiteSetting::current()->social_facebook);
    }
}
