<?php

namespace Tests\Feature;

use App\Filament\Pages\LajukasirAnalytics;
use App\Filament\Resources\Lajukasir\FaqResource;
use App\Filament\Resources\Lajukasir\FeatureResource;
use App\Filament\Resources\Lajukasir\PackageResource;
use App\Filament\Resources\Lajukasir\ScreenshotResource;
use App\Filament\Resources\Lajukasir\SettingResource;
use App\Filament\Resources\Lajukasir\SettingResource\Pages\EditSetting;
use App\Filament\Resources\Lajukasir\StepResource;
use App\Filament\Resources\Lajukasir\TestimonialResource;
use App\Filament\Widgets\Lajukasir\DeviceBreakdownChart;
use App\Filament\Widgets\Lajukasir\TopPagesTable;
use App\Filament\Widgets\Lajukasir\TrafficSourcesChart;
use App\Filament\Widgets\Lajukasir\VisitorChart;
use App\Filament\Widgets\Lajukasir\VisitorStatsOverview;
use App\Models\AnalyticsEvent;
use App\Models\Lajukasir\Package;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Menu grup "LajuKasir" di panel admin.
 *
 * Isi halaman LajuKasir berdiri sendiri dari isi landing page DigiPro Solution,
 * jadi menunya pun dikumpulkan di satu grup supaya editor tidak tertukar.
 */
class LajukasirAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Panelnya hanya dilayani di lingkungan lokal, lihat
        // Filament\Http\Middleware\Authenticate.
        config(['app.env' => 'local']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    /**
     * @return array<int, string>
     */
    private function lajukasirMenuLabels(): array
    {
        $labels = [];

        foreach (Filament::getCurrentPanel()->getNavigation() as $group) {
            if ($group->getLabel() !== 'LajuKasir') {
                continue;
            }

            foreach ($group->getItems() as $item) {
                $labels[] = $item->getLabel();
            }
        }

        return $labels;
    }

    public function test_the_lajukasir_menus_are_collected_in_their_own_navigation_group(): void
    {
        $this->assertSame([
            'Pengaturan LajuKasir',
            'Fitur Unggulan',
            'Paket Harga',
            'Cara Mulai',
            'Tampilan Aplikasi',
            'Pertanyaan Umum',
            'Testimoni',
            'Analitik LajuKasir',
        ], $this->lajukasirMenuLabels());
    }

    public function test_the_settings_screen_creates_its_row_on_demand(): void
    {
        $this->assertSame(0, Setting::query()->count());

        $this->get(SettingResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Pengaturan LajuKasir');

        $this->assertSame(1, Setting::query()->count());
    }

    public function test_saving_the_lajukasir_settings_lands_on_the_settings_screen(): void
    {
        // Pengaturan LajuKasir hanya satu baris, jadi layar penyuntingannya
        // sekaligus menjadi halaman daftarnya.
        Livewire::test(EditSetting::class)
            ->fillForm([
                'site_name' => 'LajuKasir Pro',
                'whatsapp_number' => '0896-1234-5678',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(SettingResource::getUrl('index'));

        $this->assertSame('LajuKasir Pro', Setting::current()->site_name);
    }

    public function test_every_lajukasir_content_menu_is_reachable(): void
    {
        $this->get(FeatureResource::getUrl('index'))->assertOk();
        $this->get(PackageResource::getUrl('index'))->assertOk();
        $this->get(StepResource::getUrl('index'))->assertOk();
        $this->get(ScreenshotResource::getUrl('index'))->assertOk();
        $this->get(FaqResource::getUrl('index'))->assertOk();
        $this->get(TestimonialResource::getUrl('index'))->assertOk();
        $this->get(LajukasirAnalytics::getUrl())->assertOk();
    }

    public function test_a_new_package_is_stored_and_lands_on_the_list_page(): void
    {
        Livewire::test(PackageResource::getPages()['create']->getPage())
            ->fillForm([
                'name' => 'Paket Warung',
                'price' => 'Rp 750.000',
                'order_column' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(PackageResource::getUrl('index'));

        $this->assertSame(1, Package::query()->count());
    }

    public function test_editing_and_deleting_a_package_lands_on_the_list_page(): void
    {
        $package = Package::create(['name' => 'Paket Warung', 'order_column' => 0]);

        Livewire::test(PackageResource::getPages()['edit']->getPage(), ['record' => $package->getKey()])
            ->fillForm(['name' => 'Paket Warung Besar'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(PackageResource::getUrl('index'));

        $this->assertSame('Paket Warung Besar', $package->refresh()->name);

        Livewire::test(PackageResource::getPages()['edit']->getPage(), ['record' => $package->getKey()])
            ->callAction(DeleteAction::class)
            ->assertRedirect(PackageResource::getUrl('index'));

        $this->assertDatabaseMissing('lajukasir_packages', ['id' => $package->getKey()]);
    }

    public function test_the_lajukasir_analytics_page_counts_only_its_own_subdomain(): void
    {
        $host = AnalyticsEvent::lajukasirHost();

        AnalyticsEvent::create([
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'host' => $host,
            'device' => AnalyticsEvent::DEVICE_MOBILE,
            'visited_on' => AnalyticsEvent::today()->toDateString(),
            'visitor' => hash('sha256', 'pengunjung-lajukasir'),
        ]);

        AnalyticsEvent::create([
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'host' => null,
            'device' => AnalyticsEvent::DEVICE_DESKTOP,
            'visited_on' => AnalyticsEvent::today()->toDateString(),
            'visitor' => hash('sha256', 'pengunjung-digipro'),
        ]);

        $from = AnalyticsEvent::today();

        // Satu pengunjung dari subdomain LajuKasir, satu lagi dari DigiPro
        // Solution yang tidak ikut dihitung di halaman ini.
        $this->assertSame(1, AnalyticsEvent::uniqueVisitors($from, null, AnalyticsEvent::SITE_LAJUKASIR));
        $this->assertSame(1, AnalyticsEvent::uniqueVisitors($from));

        $this->get(LajukasirAnalytics::getUrl())
            ->assertOk()
            ->assertSee('Analitik LajuKasir')
            ->assertSee($host);
    }

    public function test_the_lajukasir_widgets_are_not_added_to_the_main_dashboard(): void
    {
        // Kelimanya hanya didaftarkan oleh halaman Analitik LajuKasir, sehingga
        // dasbor DigiPro Solution tidak menampilkan angka dua kali.
        $this->assertFalse(VisitorStatsOverview::isDiscovered());
        $this->assertFalse(VisitorChart::isDiscovered());
        $this->assertFalse(TopPagesTable::isDiscovered());
        $this->assertFalse(TrafficSourcesChart::isDiscovered());
        $this->assertFalse(DeviceBreakdownChart::isDiscovered());
    }
}
