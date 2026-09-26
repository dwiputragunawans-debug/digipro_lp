<?php

namespace Tests\Feature;

use App\Models\Feature as DigiproFeature;
use App\Models\Lajukasir\Feature;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tag SEO, sitemap.xml dan robots.txt halaman LajuKasir.
 *
 * Alamat kanonisnya menunjuk ke subdomain LajuKasir sendiri, terpisah dari
 * alamat DigiPro Solution, supaya mesin pencari tidak melihat keduanya sebagai
 * satu halaman yang sama.
 */
class LajukasirSeoTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'lajukasir.digipro-solution.com';

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    private function url(string $path = '/'): string
    {
        return 'http://'.self::HOST.$path;
    }

    public function test_the_page_carries_usable_seo_tags_before_the_admin_configures_them(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('<title>'.Setting::DEFAULT_SITE_NAME.' - '.Setting::DEFAULT_META_TITLE_SUFFIX.'</title>', $html);
        $this->assertStringContainsString(Setting::DEFAULT_META_DESCRIPTION, $html);
        $this->assertStringContainsString(Setting::DEFAULT_META_KEYWORDS, $html);
        $this->assertStringContainsString('rel="canonical" href="'.config('lajukasir.url').'"', $html);
        $this->assertStringContainsString('index, follow', $html);
        $this->assertStringContainsString('application/ld+json', $html);
    }

    public function test_the_canonical_address_points_at_the_lajukasir_subdomain(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        // Alamat DigiPro Solution tidak boleh muncul sebagai alamat kanonis di
        // halaman ini, karena keduanya halaman yang berbeda.
        $this->assertStringNotContainsString('href="'.config('seo.url').'"', $html);
        $this->assertStringContainsString('href="'.config('lajukasir.url').'"', $html);
    }

    public function test_the_settings_screen_values_are_used(): void
    {
        Setting::current()->update([
            'site_name' => 'LajuKasir Pro',
            'meta_title' => 'Kasir Toko Tanpa Internet',
            'meta_description' => 'Deskripsi khusus halaman LajuKasir.',
            'meta_keywords' => 'kasir, toko',
        ]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('<title>Kasir Toko Tanpa Internet</title>', $html);
        $this->assertStringContainsString('Deskripsi khusus halaman LajuKasir.', $html);
        $this->assertStringContainsString('kasir, toko', $html);
    }

    public function test_the_address_from_the_settings_screen_wins_over_the_configured_subdomain(): void
    {
        Setting::current()->update(['site_url' => 'https://kasir.example.test']);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('rel="canonical" href="https://kasir.example.test"', $html);
    }

    public function test_the_sitemap_and_robots_txt_point_at_the_lajukasir_subdomain(): void
    {
        $sitemap = $this->get($this->url('/sitemap.xml'))->assertOk();

        $this->assertStringContainsString('<loc>'.config('lajukasir.url').'/</loc>', $sitemap->getContent());

        $robots = $this->get($this->url('/robots.txt'))->assertOk();

        $this->assertStringContainsString('Sitemap: '.config('lajukasir.url').'/sitemap.xml', $robots->getContent());
        $this->assertStringContainsString('Disallow: /admin', $robots->getContent());
    }

    public function test_the_lajukasir_sitemap_tracks_its_own_content_changes(): void
    {
        // Konten landing page DigiPro Solution diubah hari ini.
        DigiproFeature::create([
            'icon' => 'heroicon-o-bolt',
            'title' => 'Fitur DigiPro',
            'description' => 'Deskripsi fitur DigiPro.',
            'order_column' => 0,
        ]);

        // Sedangkan pengaturan LajuKasir terakhir diubah sepuluh hari lalu dan
        // kontennya tiga hari lalu.
        Setting::current()->forceFill(['updated_at' => now()->subDays(10)])->save();

        $feature = Feature::create(['title' => 'Fitur baru', 'order_column' => 0]);

        $feature->forceFill(['updated_at' => now()->subDays(3)])->save();

        Setting::forgetCurrent();

        $sitemap = $this->get($this->url('/sitemap.xml'))->assertOk();

        // Tanggal perubahannya diambil dari konten halaman ini, bukan dari
        // konten landing page DigiPro Solution yang baru saja diperbarui.
        $this->assertStringContainsString('<lastmod>'.now()->subDays(3)->toDateString().'</lastmod>', $sitemap->getContent());
        $this->assertStringNotContainsString('<lastmod>'.now()->toDateString().'</lastmod>', $sitemap->getContent());
    }

    public function test_the_page_can_be_closed_to_search_engines_while_it_is_being_prepared(): void
    {
        Setting::current()->update(['robots_index' => false]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('noindex, nofollow', $html);

        $robots = $this->get($this->url('/robots.txt'))->assertOk();

        $robots->assertSee('Disallow: /', false);
        $robots->assertDontSee('Allow: /', false);
        $robots->assertDontSee('Sitemap:', false);
    }
}
