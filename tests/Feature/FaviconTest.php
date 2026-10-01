<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Favicon: ikon kecil di tab browser, riwayat dan bookmark.
 *
 * Berkasnya diunggah di menu Pengaturan Situs (DigiPro Solution) dan Pengaturan
 * LajuKasir. Selama belum ada, logonya yang dipakai sebagai ikon, sehingga tab
 * browser tidak tampil kosong.
 */
class FaviconTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'lajukasir.digipro-solution.com';

    private const ICON_FILE = 'favicon-test.png';

    private const LOGO_FILE = 'favicon-test-logo.png';

    private const SVG_FILE = 'favicon-test.svg';

    private const UNKNOWN_FILE = 'favicon-test.bmp';

    /**
     * PNG transparan 1x1 piksel.
     */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

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
        Storage::disk('public')->delete([self::ICON_FILE, self::LOGO_FILE, self::SVG_FILE, self::UNKNOWN_FILE]);

        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    public function test_the_landing_page_has_no_icon_tag_before_anything_is_uploaded(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('<link rel="icon"', $html);
        $this->assertStringNotContainsString('apple-touch-icon', $html);
    }

    public function test_the_uploaded_favicon_is_rendered_in_the_landing_page_head(): void
    {
        $this->store(self::ICON_FILE);

        SiteSetting::create(['favicon' => self::ICON_FILE]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertIconLink($html, self::ICON_FILE, 'image/png');

        // Ikon yang sama dipakai pengunjung yang menyimpan halamannya ke layar
        // utama iPhone atau iPad.
        $this->assertStringContainsString('<link rel="apple-touch-icon" href="/storage/'.self::ICON_FILE.'">', $html);
    }

    public function test_the_logo_is_used_as_the_icon_while_the_favicon_is_still_empty(): void
    {
        $this->store(self::LOGO_FILE);

        SiteSetting::create(['logo' => self::LOGO_FILE, 'logo_adaptive' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertIconLink($html, self::LOGO_FILE, 'image/png');
    }

    public function test_the_uploaded_favicon_wins_over_the_logo(): void
    {
        $this->store(self::ICON_FILE);
        $this->store(self::LOGO_FILE);

        SiteSetting::create(['favicon' => self::ICON_FILE, 'logo' => self::LOGO_FILE]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="/storage/'.self::ICON_FILE.'"', $html);
        $this->assertStringNotContainsString('rel="icon" href="/storage/'.self::LOGO_FILE.'"', $html);
    }

    public function test_an_icon_that_is_gone_from_the_disk_falls_back_to_the_logo(): void
    {
        $this->store(self::LOGO_FILE);

        SiteSetting::create(['favicon' => 'favicon-hilang.png', 'logo' => self::LOGO_FILE]);

        $setting = SiteSetting::current();

        $this->assertSame(self::LOGO_FILE, $setting->faviconPath());
        $this->assertSame('/storage/'.self::LOGO_FILE, $setting->faviconUrl());
        $this->assertStringContainsString('/storage/'.self::LOGO_FILE, $this->get('/')->assertOk()->getContent());
    }

    public function test_an_svg_icon_is_labelled_as_svg_and_skipped_on_apple_devices(): void
    {
        Storage::disk('public')->put(
            self::SVG_FILE,
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1"><rect width="1" height="1" /></svg>',
        );

        SiteSetting::create(['favicon' => self::SVG_FILE]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertIconLink($html, self::SVG_FILE, 'image/svg+xml');

        // Apple belum menerima SVG sebagai ikon layar utama.
        $this->assertStringNotContainsString('apple-touch-icon', $html);
    }

    public function test_a_file_with_an_unknown_extension_is_left_without_a_type(): void
    {
        Storage::disk('public')->put(self::UNKNOWN_FILE, 'bukan gambar yang dikenal browser');

        $setting = new SiteSetting(['favicon' => self::UNKNOWN_FILE]);

        $this->assertSame('/storage/'.self::UNKNOWN_FILE, $setting->faviconUrl());
        // Tanpa type, browser yang menebak sendiri jenis berkasnya.
        $this->assertNull($setting->faviconType());
        $this->assertNull($setting->appleTouchIconUrl());
    }

    public function test_the_lajukasir_page_uses_its_own_favicon(): void
    {
        $this->store(self::ICON_FILE);

        Setting::current()->update(['favicon' => self::ICON_FILE]);
        Setting::forgetCurrent();

        $html = $this->get('http://'.self::HOST.'/')->assertOk()->getContent();

        $this->assertIconLink($html, self::ICON_FILE, 'image/png');
    }

    public function test_the_settings_screen_saves_the_favicon(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        // Baris pengaturannya dibuat otomatis oleh layar ini.
        SiteSetting::forgetCurrent();

        Livewire::test(EditSiteSetting::class)
            ->assertSee('Favicon', false)
            ->fillForm(['favicon' => UploadedFile::fake()->image('favicon.png', 512, 512)])
            ->call('save')
            ->assertHasNoFormErrors();

        $stored = SiteSetting::current()->favicon;

        $this->assertNotNull($stored);
        $this->assertStringEndsWith('.png', $stored);
        // Nama berkasnya diacak Filament, jadi yang diperiksa keberadaannya.
        Storage::disk('public')->assertExists($stored);
    }

    /**
     * Tag <link rel="icon"> dengan berkas dan jenis gambar yang diharapkan.
     *
     * Spasi di dalam tag tidak diperiksa, karena Blade menyisakan spasi di
     * sekitar @if yang menyusun atribut type.
     */
    private function assertIconLink(string $html, string $file, string $type): void
    {
        $this->assertMatchesRegularExpression(
            '/<link rel="icon" href="\/storage\/'.preg_quote($file, '/').'"\s+type="'.preg_quote($type, '/').'"\s*>/',
            $html,
            'Tag <link rel="icon"> untuk '.$file.' tidak ada di dalam <head>.',
        );
    }

    private function store(string $path): void
    {
        Storage::disk('public')->put($path, base64_decode(self::PNG));
    }
}
