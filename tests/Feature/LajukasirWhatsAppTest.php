<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use App\Support\Analytics\VisitorRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol WhatsApp dan pencatatan analitik halaman LajuKasir.
 *
 * Kartu obrolannya sama persis dengan yang dipakai situs DigiPro Solution;
 * yang membedakan hanya nomor, sapaan dan pesannya, serta halaman mana yang
 * dihitung di dasbor.
 */
class LajukasirWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'lajukasir.digipro-solution.com';

    private const MOBILE_UA = 'Mozilla/5.0 (Linux; Android 12; SM-A125F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

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

    public function test_the_floating_whatsapp_card_uses_the_lajukasir_defaults(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('data-whatsapp-widget', $html);
        $this->assertStringContainsString(Setting::DEFAULT_WHATSAPP_GREETING, $html);
        $this->assertStringContainsString('Mulai Konsultasi', $html);
        // Kartu obrolannya masih tertutup selama tombolnya belum ditekan.
        $this->assertStringContainsString('aria-expanded="false"', $html);
    }

    public function test_the_whatsapp_card_uses_the_number_and_message_from_the_settings(): void
    {
        Setting::current()->update([
            'whatsapp_number' => '0896-1234-5678',
            'whatsapp_greeting' => 'Halo, mau tanya LajuKasir?',
            'whatsapp_message' => 'Saya mau tanya soal aplikasi kasirnya.',
        ]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        // Nomor yang ditulis dengan awalan 0 dirapikan menjadi format
        // internasional, sama seperti di situs DigiPro Solution.
        $this->assertStringContainsString('https://wa.me/6289612345678?text='.rawurlencode('Saya mau tanya soal aplikasi kasirnya.'), $html);
        $this->assertStringContainsString('Halo, mau tanya LajuKasir?', $html);
    }

    public function test_the_floating_button_can_be_turned_off_from_the_settings(): void
    {
        Setting::current()->update(['whatsapp_enabled' => false]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringNotContainsString('data-whatsapp-widget', $html);
        // Tombol "Hubungi Kami" di header tetap memakai nomor yang sama.
        $this->assertStringContainsString('https://wa.me/'.Setting::DEFAULT_WHATSAPP_NUMBER, $html);
    }

    public function test_a_visit_to_the_lajukasir_page_is_recorded_under_the_subdomain(): void
    {
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get($this->url())->assertOk();

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'host' => self::HOST,
            'device' => AnalyticsEvent::DEVICE_MOBILE,
        ]);
    }

    public function test_a_visit_from_the_local_alias_is_counted_as_the_subdomain(): void
    {
        // Alias .localhost menyajikan halaman yang sama, jadi kunjungannya harus
        // masuk hitungan LajuKasir, bukan hitungan DigiPro Solution.
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])
            ->get('http://'.config('lajukasir.local_host').'/')
            ->assertOk();

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'host' => self::HOST,
        ]);

        $today = AnalyticsEvent::today();

        $this->assertSame(1, AnalyticsEvent::uniqueVisitors($today, null, AnalyticsEvent::SITE_LAJUKASIR));
        $this->assertSame(0, AnalyticsEvent::uniqueVisitors($today));
    }

    public function test_a_click_on_a_lajukasir_whatsapp_button_is_recorded_under_the_subdomain(): void
    {
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])
            ->postJson($this->url('/analytics/whatsapp-click'), ['path' => '/'])
            ->assertNoContent();

        $this->assertDatabaseHas('analytics_events', [
            'type' => AnalyticsEvent::TYPE_WHATSAPP_CLICK,
            'path' => '/',
            'host' => self::HOST,
        ]);
    }

    public function test_the_two_pages_count_their_visitors_separately(): void
    {
        // Dua kunjungan ke halaman LajuKasir dan satu ke halaman DigiPro
        // Solution. Keduanya memakai path "/", jadi hanya kolom host yang bisa
        // membedakannya.
        //
        // Alamat DigiPro Solution ditulis lengkap karena klien pengujian
        // menyelesaikan alamat relatif memakai host permintaan sebelumnya.
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get($this->url())->assertOk();
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get($this->url())->assertOk();
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])->get('http://localhost/')->assertOk();

        $from = AnalyticsEvent::today();

        // Dasbor DigiPro Solution hanya menghitung halamannya sendiri.
        $this->assertSame(1, AnalyticsEvent::viewCount($from));

        // Sedangkan Analitik LajuKasir hanya menghitung subdomainnya.
        $this->assertSame(2, AnalyticsEvent::viewCount($from, null, AnalyticsEvent::SITE_LAJUKASIR));

        // Dicatat ketiganya, jadi tidak ada kunjungan yang hilang.
        $this->assertSame(3, AnalyticsEvent::query()->pageViews()->count());
    }

    public function test_events_from_before_the_host_column_existed_still_belong_to_digipro(): void
    {
        AnalyticsEvent::create([
            'type' => AnalyticsEvent::TYPE_PAGE_VIEW,
            'path' => '/',
            'device' => AnalyticsEvent::DEVICE_DESKTOP,
            'source' => AnalyticsEvent::DIRECT_SOURCE,
            'visited_on' => AnalyticsEvent::today()->toDateString(),
            'visitor' => hash('sha256', 'pengunjung-lama'),
        ]);

        $from = AnalyticsEvent::today();

        // Baris lama tidak punya host, jadi tetap dihitung milik DigiPro
        // Solution dan tidak ikut muncul di Analitik LajuKasir.
        $this->assertSame(1, AnalyticsEvent::uniqueVisitors($from));
        $this->assertSame(0, AnalyticsEvent::uniqueVisitors($from, null, AnalyticsEvent::SITE_LAJUKASIR));
    }

    public function test_a_whatsapp_link_without_a_message_gets_the_default_message(): void
    {
        Setting::current()->update([
            // Tautan dari tombol "bagikan ke WhatsApp": nomornya ada, pesannya
            // belum diisi.
            'hero_primary_url' => 'https://api.whatsapp.com/send/?phone=6281234567890&text&type=phone_number&app_absent=0',
        ]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        // Pesan bawaan situs yang dipakai, jadi pengunjung tidak mendarat di
        // kolom chat yang kosong. Tautannya ditulis ulang menjadi wa.me —
        // bentuk yang paling aman untuk aplikasi WhatsApp — sementara nomornya
        // tetap nomor dari tautan admin.
        $this->assertStringContainsString('https://wa.me/6281234567890?text='.rawurlencode(Setting::DEFAULT_WHATSAPP_MESSAGE), $html);
        $this->assertStringNotContainsString('app_absent', $html);
    }

    public function test_a_whatsapp_link_that_already_has_a_message_is_left_alone(): void
    {
        $url = 'https://wa.me/628111222333?text='.rawurlencode('Halo, saya mau tanya paket toko.');

        Setting::current()->update(['hero_primary_url' => $url]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString($url, $html);
    }

    public function test_the_visitor_cookie_is_the_same_one_the_digipro_page_uses(): void
    {
        $this->withHeaders(['User-Agent' => self::MOBILE_UA])
            ->get($this->url())
            ->assertCookie(VisitorRecorder::COOKIE);
    }
}
