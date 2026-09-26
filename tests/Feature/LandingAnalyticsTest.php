<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Support\Analytics\VisitorRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pencatatan kunjungan landing page dan klik tombol WhatsApp.
 */
class LandingAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const MOBILE_UA = 'Mozilla/5.0 (Linux; Android 12; SM-A125F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    private const GOOGLEBOT_UA = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    public function test_a_visit_to_the_landing_page_is_recorded_with_its_device(): void
    {
        $response = $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get('/');

        $response->assertOk();

        // Pengunjung diberi id acak di cookie, supaya kunjungan berikutnya
        // masih dihitung sebagai orang yang sama.
        $response->assertCookie(VisitorRecorder::COOKIE);

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'device' => AnalyticsEvent::DEVICE_MOBILE,
            'browser' => 'Chrome',
            'platform' => 'Android',
            'source' => AnalyticsEvent::DIRECT_SOURCE,
        ]);
    }

    public function test_the_source_of_a_visit_is_read_from_the_referer(): void
    {
        $this->withHeaders([
            'User-Agent' => self::MOBILE_UA,
            'Referer' => 'https://www.google.com/search?q=jasa+pembuatan+website',
        ])->get('/')->assertOk();

        $this->assertDatabaseHas('analytics_events', ['source' => 'Google']);
    }

    public function test_a_campaign_parameter_wins_over_the_referer(): void
    {
        $this->withHeaders([
            'User-Agent' => self::MOBILE_UA,
            'Referer' => 'https://www.google.com/search?q=jasa+pembuatan+website',
        ])->get('/?utm_source=newsletter')->assertOk();

        $this->assertDatabaseHas('analytics_events', ['source' => 'Newsletter']);
    }

    public function test_bots_and_visitors_who_opt_out_are_not_recorded(): void
    {
        $this->withHeaders(['User-Agent' => self::GOOGLEBOT_UA])->get('/')->assertOk();

        $this->withHeaders(['User-Agent' => self::MOBILE_UA, 'DNT' => '1'])->get('/')->assertOk();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_the_same_visitor_is_only_counted_once_per_day(): void
    {
        $first = $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get('/')->assertOk();

        // Nilai cookie yang sudah terenkripsi dikirim balik seperti yang
        // dilakukan browser pada kunjungan berikutnya. withUnencryptedCookie()
        // dipakai supaya kerangka tes tidak mengenkripsinya untuk kedua kalinya.
        $cookie = $first->getCookie(VisitorRecorder::COOKIE, false);

        $this->assertNotNull($cookie);

        $this->withUnencryptedCookie(VisitorRecorder::COOKIE, $cookie->getValue())
            ->withHeaders(['User-Agent' => self::MOBILE_UA])
            ->get('/')
            ->assertOk();

        $today = AnalyticsEvent::rangeStart(1);

        $this->assertSame(2, AnalyticsEvent::viewCount($today));
        $this->assertSame(1, AnalyticsEvent::uniqueVisitors($today));
    }

    public function test_a_whatsapp_click_is_recorded_with_the_page_it_came_from(): void
    {
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])
            ->postJson(route('analytics.whatsapp-click'), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_WHATSAPP_CLICK,
            'path' => '/',
            'device' => AnalyticsEvent::DEVICE_MOBILE,
        ]);
    }

    public function test_a_click_reported_by_a_bot_is_ignored(): void
    {
        $this->withHeaders(['User-Agent' => self::GOOGLEBOT_UA])
            ->postJson(route('analytics.whatsapp-click'), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_a_whatsapp_click_falls_back_to_the_page_that_sent_it(): void
    {
        $this->withHeaders([
            'User-Agent' => self::MOBILE_UA,
            'Referer' => 'https://digipro-solution.com/',
        ])->postJson(route('analytics.whatsapp-click'))->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_WHATSAPP_CLICK,
            'path' => '/',
        ]);
    }

    public function test_visitors_are_counted_per_person_for_the_summary_cards(): void
    {
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2');
        $this->record(AnalyticsEvent::TYPE_WHATSAPP_CLICK, 'visitor-2');

        $today = AnalyticsEvent::rangeStart(1);

        // Tiga halaman dibuka, tetapi hanya oleh dua orang.
        $this->assertSame(3, AnalyticsEvent::viewCount($today));
        $this->assertSame(2, AnalyticsEvent::uniqueVisitors($today));

        // Satu klik WhatsApp, dari satu orang.
        $this->assertSame(1, AnalyticsEvent::whatsAppClickCount($today));
        $this->assertSame(1, AnalyticsEvent::whatsAppClickVisitors($today));
    }

    public function test_visitors_are_counted_per_day_for_the_chart(): void
    {
        $yesterday = AnalyticsEvent::rangeStart(2)->toDateString();
        $today = AnalyticsEvent::today()->toDateString();

        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'yesterday-1', visitedOn: $yesterday);
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'yesterday-2', visitedOn: $yesterday);
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'today-1', visitedOn: $today);
        $this->record(AnalyticsEvent::TYPE_WHATSAPP_CLICK, 'today-1', visitedOn: $today);

        $perDay = AnalyticsEvent::perDay(2);

        $this->assertCount(2, $perDay['labels']);
        $this->assertSame([2, 1], $perDay['visitors']);
        $this->assertSame([2, 1], $perDay['views']);
        $this->assertSame([0, 1], $perDay['clicks']);
    }

    public function test_the_top_pages_are_grouped_by_path(): void
    {
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1', path: '/');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2', path: '/');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-3', path: '/solusi');
        $this->record(AnalyticsEvent::TYPE_WHATSAPP_CLICK, 'visitor-3', path: '/solusi');

        $pages = AnalyticsEvent::topPages();

        $this->assertSame(['/', '/solusi'], $pages->pluck('path')->all());
        $this->assertSame(2, (int) $pages->first()->views);
        $this->assertSame(2, (int) $pages->first()->visitors);

        // Klik WhatsApp bukan kunjungan halaman, jadi tidak ikut dihitung.
        $this->assertSame(1, (int) $pages->last()->views);
    }

    public function test_the_sources_and_devices_are_grouped_for_the_charts(): void
    {
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-1', device: AnalyticsEvent::DEVICE_MOBILE, source: 'Google');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-2', device: AnalyticsEvent::DEVICE_MOBILE, source: 'Google');
        $this->record(AnalyticsEvent::TYPE_PAGE_VIEW, 'visitor-3', device: AnalyticsEvent::DEVICE_DESKTOP, source: AnalyticsEvent::DIRECT_SOURCE);

        // Laporan klik tombol WhatsApp datang dari halaman yang sama, jadi tidak
        // menambah asal pengunjung yang baru.
        $this->record(AnalyticsEvent::TYPE_WHATSAPP_CLICK, 'visitor-1', device: AnalyticsEvent::DEVICE_MOBILE, source: AnalyticsEvent::DIRECT_SOURCE);

        $sources = AnalyticsEvent::sources();

        $this->assertSame(['Google', AnalyticsEvent::DIRECT_SOURCE], $sources->pluck('source')->all());
        $this->assertSame([2, 1], $sources->pluck('visitors')->map(fn ($visitors): int => (int) $visitors)->all());

        $devices = AnalyticsEvent::devices();

        $this->assertSame([AnalyticsEvent::DEVICE_MOBILE, AnalyticsEvent::DEVICE_DESKTOP], $devices->pluck('device')->all());
        $this->assertSame('Ponsel', AnalyticsEvent::deviceLabel($devices->first()->device));
        $this->assertSame('Tidak dikenal', AnalyticsEvent::deviceLabel(null));
    }

    /**
     * Catat satu kejadian tanpa lewat HTTP, supaya agregasi dasbornya bisa
     * diuji dengan data yang pasti.
     */
    private function record(
        string $type,
        string $visitor,
        string $path = '/',
        string $device = AnalyticsEvent::DEVICE_DESKTOP,
        string $source = AnalyticsEvent::DIRECT_SOURCE,
        ?string $visitedOn = null,
    ): AnalyticsEvent {
        return AnalyticsEvent::create([
            'type' => $type,
            'path' => $path,
            'device' => $device,
            'source' => $source,
            'visited_on' => $visitedOn ?? AnalyticsEvent::today()->toDateString(),
            'visitor' => hash('sha256', $visitor),
        ]);
    }
}
