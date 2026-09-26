<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    use RefreshDatabase;

    private const FILE_NAME = 'site-logo-test.png';

    /**
     * A transparent 1x1 PNG, so the detected aspect ratio is 1.
     */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        // The settings row is remembered for the duration of a request, and a
        // single PHP process runs every test, so start each test from a clean slate.
        SiteSetting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete(self::FILE_NAME);

        SiteSetting::forgetCurrent();

        parent::tearDown();
    }

    public function test_the_logo_is_rendered_in_the_landing_page_header_and_footer(): void
    {
        $this->storeLogo();

        SiteSetting::create([
            'site_name' => 'DigiPro',
            'logo' => self::FILE_NAME,
            'logo_adaptive' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();

        $html = $response->getContent();

        // Once in the header and once in the footer.
        $this->assertSame(2, substr_count($html, 'role="img"'));
        $this->assertSame(2, substr_count($html, 'aria-label="DigiPro"'));
        $this->assertStringContainsString("url('/storage/".self::FILE_NAME."')", $html);

        // The logo is filled with the current text colour, so it takes on the
        // colour of the header it sits in instead of keeping its own colours.
        $this->assertSame(2, substr_count($html, 'background-color: currentColor'));

        // Dimensions of the uploaded file are detected, so it is not squashed.
        $this->assertSame(2, substr_count($html, 'aspect-ratio: 1;'));
    }

    public function test_the_logo_keeps_its_own_colours_when_the_adaptive_colour_is_turned_off(): void
    {
        $this->storeLogo();

        SiteSetting::create([
            'site_name' => 'DigiPro',
            'logo' => self::FILE_NAME,
            'logo_adaptive' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(2, substr_count($html, 'src="/storage/'.self::FILE_NAME.'"'));
        $this->assertStringNotContainsString('background-color: currentColor', $html);
    }

    public function test_the_landing_page_falls_back_to_the_text_brand_without_a_logo(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $this->assertStringNotContainsString('background-color: currentColor', $response->getContent());
        $response->assertSee('digi<span class="text-blue-600">pro</span>', false);
        $response->assertSee('digi<span class="text-blue-500">pro</span>', false);
    }

    public function test_the_admin_panel_uses_the_logo_as_its_brand_logo(): void
    {
        $this->storeLogo();

        SiteSetting::create([
            'site_name' => 'DigiPro',
            'logo' => self::FILE_NAME,
            'logo_adaptive' => true,
        ]);

        $response = $this->get('/admin/login');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString("url('/storage/".self::FILE_NAME."')", $html);
        $this->assertStringContainsString('background-color: currentColor', $html);
    }

    public function test_the_admin_panel_falls_back_to_the_site_name_without_a_logo(): void
    {
        SiteSetting::create(['site_name' => 'DigiPro Studio']);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('DigiPro Studio', false);
    }

    public function test_the_site_setting_page_creates_the_settings_row_on_demand(): void
    {
        $this->actingAs(User::factory()->create());

        $this->assertDatabaseCount('site_settings', 0);

        Livewire::test(EditSiteSetting::class);

        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_the_site_setting_page_saves_the_logo_settings(): void
    {
        $this->actingAs(User::factory()->create());

        $this->storeLogo();

        // Pretend a logo has already been uploaded, like the page the admin
        // opens on the next visit.
        SiteSetting::create([
            'site_name' => 'DigiPro',
            'logo' => self::FILE_NAME,
            'logo_adaptive' => true,
        ]);

        Livewire::test(EditSiteSetting::class)
            ->assertSee('Header landing page', false)
            ->set('data.site_name', 'DigiPro Studio')
            ->set('data.logo_adaptive', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_settings', [
            'site_name' => 'DigiPro Studio',
            'logo' => self::FILE_NAME,
            'logo_adaptive' => false,
        ]);
    }

    public function test_the_site_setting_page_is_reachable_as_the_single_settings_screen(): void
    {
        // App\Models\User does not implement FilamentUser, and Filament only lets
        // such users into a panel while the app environment is "local" (see
        // Filament\Http\Middleware\Authenticate), which is how this app runs.
        config(['app.env' => 'local']);

        $this->actingAs(User::factory()->create());

        $this->get('/admin/site-settings')
            ->assertOk()
            ->assertSee('Pengaturan Situs', false)
            ->assertDontSee('Buat Pengaturan Situs', false);
    }

    public function test_a_logo_file_that_is_gone_from_the_disk_does_not_break_the_pages(): void
    {
        SiteSetting::create([
            'site_name' => 'DigiPro',
            'logo' => 'ghost-logo.png',
        ]);

        // The landing page falls back to the text brand instead of a broken image.
        $this->get('/')
            ->assertOk()
            ->assertSee('digi<span class="text-blue-600">pro</span>', false)
            ->assertDontSee('ghost-logo.png', false);

        $this->actingAs(User::factory()->create());

        Livewire::test(EditSiteSetting::class)
            ->assertSee('Belum ada logo tersimpan', false);
    }

    private function storeLogo(): void
    {
        Storage::disk('public')->put(self::FILE_NAME, base64_decode(self::PNG));
    }
}
