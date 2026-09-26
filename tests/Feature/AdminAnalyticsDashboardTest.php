<?php

namespace Tests\Feature;

use App\Filament\Widgets\DeviceBreakdownChart;
use App\Filament\Widgets\TopPagesTable;
use App\Filament\Widgets\TrafficSourcesChart;
use App\Filament\Widgets\VisitorChart;
use App\Filament\Widgets\VisitorStatsOverview;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Widget analitik di dasbor panel admin.
 */
class AdminAnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // App\Models\User does not implement FilamentUser, and Filament only
        // lets such users into a panel while the app environment is "local".
        config(['app.env' => 'local']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());
    }

    public function test_the_dashboard_renders_every_analytics_widget(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(VisitorStatsOverview::class)
            ->assertSeeLivewire(VisitorChart::class)
            ->assertSeeLivewire(TopPagesTable::class)
            ->assertSeeLivewire(TrafficSourcesChart::class)
            ->assertSeeLivewire(DeviceBreakdownChart::class);
    }

    public function test_the_summary_cards_show_todays_visitors_and_whats_app_clicks(): void
    {
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1', path: '/');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2', path: '/');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2', path: '/solusi');
        $this->record(AnalyticsEvent::TYPE_WHATSAPP_CLICK, 'visitor-2', path: '/');

        Livewire::test(VisitorStatsOverview::class)
            ->assertOk()
            ->assertSee('Pengunjung hari ini')
            ->assertSee('3 halaman dibuka')
            ->assertSee('Terbanyak: /')
            ->assertSee('Klik WhatsApp (30 hari)')
            ->assertSee('1 pengunjung menghubungi');
    }

    public function test_the_top_pages_widget_lists_the_pages_with_their_visitors(): void
    {
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1', path: '/');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2', path: '/solusi');

        Livewire::test(TopPagesTable::class)
            ->assertOk()
            ->assertSee('Halaman yang paling banyak dibuka')
            ->assertSee('/solusi')
            ->assertSee('Pengunjung');
    }

    public function test_the_widgets_render_before_there_is_any_data(): void
    {
        Livewire::test(VisitorStatsOverview::class)
            ->assertOk()
            ->assertSee('Belum ada kunjungan');

        Livewire::test(VisitorChart::class)->assertOk();
        Livewire::test(TopPagesTable::class)->assertOk()->assertSee('Belum ada kunjungan');
        Livewire::test(TrafficSourcesChart::class)->assertOk();
        Livewire::test(DeviceBreakdownChart::class)->assertOk();
    }

    /**
     * Catat satu kejadian tanpa lewat HTTP, supaya angkanya bisa ditentukan
     * dengan pasti.
     */
    private function record(string $type, string $visitor, string $path = '/'): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'type' => $type,
            'path' => $path,
            'device' => AnalyticsEvent::DEVICE_MOBILE,
            'source' => 'Google',
            'visited_on' => AnalyticsEvent::today()->toDateString(),
            'visitor' => hash('sha256', $visitor),
        ]);
    }
}
