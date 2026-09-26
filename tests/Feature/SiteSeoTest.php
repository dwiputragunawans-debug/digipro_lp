<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pengaturan SEO: judul, deskripsi, kata kunci, pratinjau tautan, izin indeks,
 * sitemap.xml dan robots.txt.
 */
class SiteSeoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Situs ini disajikan dari domain produksinya, sementara aplikasinya masih
     * berjalan di localhost. Tag SEO harus selalu menunjuk domain produksi.
     */
    private const DOMAIN = 'https://digipro-solution.com';

    private const OG_IMAGE = 'social-preview-test.png';

    /**
     * PNG transparan 1x1 piksel.
     */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        config(['seo.url' => self::DOMAIN]);

        // Baris pengaturan diingat selama satu permintaan, sedangkan satu proses
        // PHP menjalankan semua tes, jadi setiap tes mulai dari nol.
        SiteSetting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete(self::OG_IMAGE);

        SiteSetting::forgetCurrent();

        parent::tearDown();
    }

    public function test_the_landing_page_carries_usable_seo_tags_before_the_admin_configures_anything(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('<title>'.config('app.name').' - '.SiteSetting::DEFAULT_META_TITLE_SUFFIX.'</title>', false);
        $response->assertSee('<meta name="description" content="'.SiteSetting::DEFAULT_META_DESCRIPTION.'">', false);
        $response->assertSee('<meta name="robots" content="index, follow">', false);
        $response->assertSee('<link rel="canonical" href="'.self::DOMAIN.'">', false);

        // Kata kunci bawaan, dipisah koma.
        $response->assertSee('content="jasa pembuatan website, website umkm', false);

        // Pratinjau tautan tanpa gambar: kartunya ringkas saja.
        $response->assertSee('<meta property="og:url" content="'.self::DOMAIN.'">', false);
        $response->assertSee('<meta name="twitter:card" content="summary">', false);
        $response->assertDontSee('property="og:image"', false);

        // Data terstruktur supaya Google tahu ini situs apa.
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"url":"'.self::DOMAIN.'"', false);
    }

    public function test_the_settings_screen_saves_the_seo_fields(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            // Filament menulis ampersand sebagai &amp; di dalam HTML.
            ->assertSee('SEO & Mesin Pencari')
            ->fillForm([
                'site_url' => self::DOMAIN,
                'meta_title' => 'Jasa Pembuatan Website UMKM | DigiPro Solution',
                'meta_description' => 'Website profesional untuk UMKM, dikerjakan cepat dan mudah dikelola.',
                'meta_keywords' => 'jasa website, website umkm',
                'robots_index' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        SiteSetting::forgetCurrent();

        $setting = SiteSetting::current();

        $this->assertSame(self::DOMAIN, $setting->siteUrl());
        $this->assertSame('Jasa Pembuatan Website UMKM | DigiPro Solution', $setting->metaTitle());
        $this->assertSame('Website profesional untuk UMKM, dikerjakan cepat dan mudah dikelola.', $setting->metaDescription());
        $this->assertSame(['jasa website', 'website umkm'], $setting->metaKeywords());
        $this->assertFalse($setting->isIndexable());
    }

    public function test_the_landing_page_uses_the_values_from_the_settings_screen(): void
    {
        SiteSetting::create([
            'site_name' => 'DigiPro Solution',
            'meta_title' => 'Judul khusus dari panel',
            'meta_description' => 'Deskripsi khusus dari panel.',
            'meta_keywords' => 'kata kunci satu, kata kunci dua',
            'robots_index' => false,
        ]);

        SiteSetting::forgetCurrent();

        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Judul khusus dari panel</title>', false)
            ->assertSee('<meta name="description" content="Deskripsi khusus dari panel.">', false)
            ->assertSee('content="kata kunci satu, kata kunci dua"', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_the_address_from_the_settings_screen_wins_over_the_configured_domain(): void
    {
        SiteSetting::create(['site_url' => 'https://www.digipro-solution.com/']);

        SiteSetting::forgetCurrent();

        // Garis miring di akhir dibuang supaya tidak jadi alamat ganda.
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://www.digipro-solution.com">', false);
    }

    public function test_the_social_preview_image_is_published_as_an_absolute_url(): void
    {
        Storage::disk('public')->put(self::OG_IMAGE, base64_decode(self::PNG));

        SiteSetting::create([
            'site_name' => 'DigiPro Solution',
            'og_image' => self::OG_IMAGE,
        ]);

        SiteSetting::forgetCurrent();

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.self::DOMAIN.'/storage/'.self::OG_IMAGE.'">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<meta name="twitter:image" content="'.self::DOMAIN.'/storage/'.self::OG_IMAGE.'">', false);
    }

    public function test_the_sitemap_and_robots_txt_point_at_the_configured_domain(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk();

        $sitemap->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $sitemap->assertSee('<loc>'.self::DOMAIN.'/</loc>', false);
        $sitemap->assertSee('<lastmod>'.now()->toDateString().'</lastmod>', false);
        $sitemap->assertSee('<changefreq>weekly</changefreq>', false);
        $sitemap->assertSee('<priority>1.0</priority>', false);

        $robots = $this->get('/robots.txt')->assertOk();

        $robots->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $robots->assertSee('User-agent: *', false);
        $robots->assertSee('Allow: /', false);
        $robots->assertSee('Disallow: /admin', false);
        $robots->assertSee('Sitemap: '.self::DOMAIN.'/sitemap.xml', false);
    }

    public function test_the_site_can_be_closed_to_search_engines_while_it_is_being_prepared(): void
    {
        SiteSetting::create([
            'site_name' => 'DigiPro Solution',
            'robots_index' => false,
        ]);

        SiteSetting::forgetCurrent();

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $robots = $this->get('/robots.txt')->assertOk();

        $robots->assertSee('Disallow: /', false);
        $robots->assertDontSee('Allow: /', false);
        $robots->assertDontSee('Sitemap:', false);
    }
}
