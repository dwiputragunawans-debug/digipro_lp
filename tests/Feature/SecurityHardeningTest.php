<?php

namespace Tests\Feature;

use App\Filament\Resources\Lajukasir\SettingResource\Pages\EditSetting;
use App\Models\AnalyticsEvent;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Celah keamanan yang dijaga proyek ini sebelum dipasang di hosting: siapa yang
 * boleh membuka panel admin, akun bawaan, header keamanan, CSRF, pembanjiran
 * tabel analitik dan unggahan berkas SVG.
 */
class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER_UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const CLEAN_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 12"><rect width="40" height="12" fill="#087b6a" /></svg>';

    private const SCRIPTED_SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 12" onload="alert(document.cookie)"><rect width="40" height="12" /></svg>';

    protected function setUp(): void
    {
        parent::setUp();

        // Baris pengaturan diingat selama satu permintaan, sedangkan satu proses
        // PHP menjalankan semua tes.
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    /**
     * Panel admin hanya boleh dibuka pemilik akun yang emailnya terdaftar di
     * ADMIN_EMAILS, juga setelah situsnya berjalan di produksi.
     */
    public function test_only_listed_emails_may_open_the_admin_panel_in_production(): void
    {
        config([
            'app.env' => 'production',
            'security.admin_emails' => ['owner@digipro-solution.com'],
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create(['email' => 'owner@digipro-solution.com']))
            ->get('/admin')
            ->assertOk();

        // Akun lama yang sudah tidak dipakai tidak bisa masuk, walaupun kata
        // sandinya benar.
        $this->actingAs(User::factory()->create(['email' => 'mantan-karyawan@example.com']))
            ->get('/admin')
            ->assertForbidden();
    }

    /**
     * Daftar yang masih kosong tidak mengunci pemilik situs keluar dari
     * panelnya sendiri; perilakunya sama seperti sebelum daftar ini ada.
     */
    public function test_the_panel_still_opens_when_no_email_list_is_configured(): void
    {
        config([
            'app.env' => 'production',
            'security.admin_emails' => [],
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create(['email' => 'siapa-saja@example.com']))
            ->get('/admin')
            ->assertOk();
    }

    /**
     * Akun admin dibuat dari ADMIN_EMAIL dan ADMIN_PASSWORD, bukan dari nilai
     * bawaan yang bisa ditebak.
     */
    public function test_the_seeder_creates_the_admin_from_the_environment(): void
    {
        config([
            'security.seed_admin_email' => 'Admin@DigiPro-Solution.com',
            'security.seed_admin_password' => 'kata-sandi-panjang-2026',
        ]);

        $this->seed(DatabaseSeeder::class);

        $user = User::query()->where('email', 'admin@digipro-solution.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('kata-sandi-panjang-2026', (string) $user->password));
    }

    public function test_the_seeder_never_creates_an_account_with_the_laravel_default_password(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_the_seeder_refuses_a_short_admin_password(): void
    {
        config([
            'security.seed_admin_email' => 'admin@digipro-solution.com',
            'security.seed_admin_password' => 'pendek',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }

    /**
     * Akun demo lama (test@example.com dengan kata sandi "password") dimatikan
     * kalau basis datanya ikut disalin dari komputer pengembang.
     */
    public function test_the_seeder_disables_the_old_demo_account(): void
    {
        $demo = User::factory()->create(['email' => 'test@example.com']);

        $this->assertTrue(Hash::check('password', (string) $demo->password));

        $this->seed(DatabaseSeeder::class);

        $this->assertFalse(Hash::check('password', (string) $demo->refresh()->password));
    }

    public function test_security_headers_are_sent_with_every_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
            // HSTS baru dikirim kalau permintaannya memang sudah lewat https.
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_is_sent_over_https(): void
    {
        $this->get('https://digipro-solution.test/')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    /**
     * Laporan klik WhatsApp hanya diterima bersama token CSRF, sehingga situs
     * lain tidak bisa mengirim laporan atas nama pengunjung.
     *
     * Laravel melewati pemeriksaan token selama tes dijalankan, jadi yang
     * diperiksa di sini adalah pemasangan middleware-nya pada route tersebut.
     */
    public function test_the_analytics_endpoint_is_guarded_by_csrf_protection(): void
    {
        $route = Route::getRoutes()->getByName('analytics.whatsapp-click');

        $this->assertNotNull($route, 'Route laporan klik WhatsApp tidak ditemukan.');

        $this->assertContains(
            PreventRequestForgery::class,
            app('router')->gatherRouteMiddleware($route),
            'Route analitik harus melewati pemeriksaan token CSRF.',
        );
    }

    /**
     * Satu alamat IP tidak bisa membanjiri tabel analitik, tetapi halamannya
     * tetap dilayani seperti biasa.
     */
    public function test_a_single_address_cannot_flood_the_analytics_table(): void
    {
        config(['security.max_recorded_views_per_minute' => 2]);

        RateLimiter::clear('analytics-views:127.0.0.1');

        foreach (range(1, 5) as $ignored) {
            $this->withHeaders(['User-Agent' => self::BROWSER_UA])->get('/')->assertOk();
        }

        $this->assertSame(2, AnalyticsEvent::query()->count());
    }

    /**
     * SVG yang memuat skrip tidak boleh tersimpan, karena skripnya akan
     * berjalan di alamat situs ini begitu berkasnya dibuka langsung.
     */
    public function test_a_scripted_svg_cannot_be_uploaded_as_a_logo(): void
    {
        $this->openTheSettingsForm();

        Livewire::test(EditSetting::class)
            ->set('data.logo', [UploadedFile::fake()->createWithContent('logo.svg', self::SCRIPTED_SVG)])
            ->call('save')
            ->assertHasFormErrors(['logo']);

        $this->assertNull(Setting::current()->logo);
    }

    /**
     * Logo SVG yang bersih tetap bisa diunggah seperti biasa.
     */
    public function test_a_clean_svg_is_still_accepted_as_a_logo(): void
    {
        $this->openTheSettingsForm();

        Livewire::test(EditSetting::class)
            ->set('data.logo', [UploadedFile::fake()->createWithContent('logo.svg', self::CLEAN_SVG)])
            ->call('save')
            ->assertHasNoFormErrors();

        $logo = Setting::current()->logo;

        $this->assertNotNull($logo);
        $this->assertStringEndsWith('.svg', $logo);
    }

    /**
     * Berkas gambar biasa tidak terpengaruh pemeriksaan SVG.
     */
    public function test_a_png_logo_is_still_accepted(): void
    {
        $this->openTheSettingsForm();

        Livewire::test(EditSetting::class)
            ->set('data.logo', [UploadedFile::fake()->image('logo.png', 40, 12)])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNotNull(Setting::current()->logo);
    }

    /**
     * Menyiapkan lingkungan form pengaturan LajuKasir seperti saat dibuka admin.
     */
    private function openTheSettingsForm(): void
    {
        // Panelnya hanya dilayani di lingkungan lokal untuk pengujian, lihat
        // App\Models\User::canAccessPanel().
        config(['app.env' => 'local', 'security.admin_emails' => []]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());
    }
}
