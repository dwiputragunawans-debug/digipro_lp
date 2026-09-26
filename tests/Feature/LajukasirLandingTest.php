<?php

namespace Tests\Feature;

use App\Models\Lajukasir\Faq;
use App\Models\Lajukasir\Feature;
use App\Models\Lajukasir\Package;
use App\Models\Lajukasir\Screenshot;
use App\Models\Lajukasir\Setting;
use App\Models\Lajukasir\Step;
use App\Models\Lajukasir\Testimonial;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Halaman LajuKasir di subdomainnya sendiri.
 *
 * Alamatnya diatur di config/lajukasir.php dan dipakai Route::domain() di
 * routes/web.php, jadi pengujian di berkas ini memakai nomor halaman tersebut,
 * bukan host bawaan pengujian (localhost).
 */
class LajukasirLandingTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'lajukasir.digipro-solution.com';

    /**
     * Logo uji: PNG transparan berukuran 1x1 piksel, sama seperti yang dipakai
     * pengujian logo DigiPro Solution, supaya rasionya terbaca dan bernilai 1.
     */
    private const LOGO_FILE = 'lajukasir-logo-test.png';

    private const LOGO_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        // Pengaturan kedua situs diingat selama satu permintaan, sedangkan satu
        // proses PHP menjalankan seluruh pengujian.
        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete(self::LOGO_FILE);

        SiteSetting::forgetCurrent();
        Setting::forgetCurrent();

        parent::tearDown();
    }

    private function url(string $path = '/'): string
    {
        return 'http://'.self::HOST.$path;
    }

    public function test_the_subdomain_serves_the_lajukasir_page(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        // Nilai bawaan langsung terpakai sebelum admin mengisi apa pun.
        $this->assertStringContainsString('Kasir Toko yang Tetap Jalan Walau Internet Mati', $html);
        $this->assertStringContainsString(Setting::DEFAULT_SITE_NAME, $html);
        $this->assertStringContainsString('https://wa.me/'.Setting::DEFAULT_WHATSAPP_NUMBER, $html);
    }

    public function test_the_main_domain_still_serves_the_digipro_page(): void
    {
        // Route DigiPro Solution tidak dibatasi domain, jadi urutan
        // pendaftarannya penting: grup LajuKasir harus didaftarkan lebih dahulu,
        // kalau tidak halaman DigiPro yang muncul di subdomain.
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('digi<span', $html);
        $this->assertStringNotContainsString('LajuKasir', $html);
    }

    public function test_the_local_alias_serves_the_lajukasir_page(): void
    {
        // Alias .localhost menunjuk kembali ke komputer sendiri, jadi halamannya
        // bisa dibuka di http://lajukasir.localhost:8000 tanpa mengubah /etc/hosts.
        $html = $this->get('http://'.config('lajukasir.local_host').'/')->assertOk()->getContent();

        $this->assertStringContainsString('Kasir Toko yang Tetap Jalan Walau Internet Mati', $html);

        // Halaman DigiPro Solution tidak boleh ikut pindah ke alamat ini.
        $this->assertStringNotContainsString('digi<span', $html);
    }

    public function test_the_alias_is_listed_after_the_real_subdomain(): void
    {
        // Urutan di config/lajukasir.php menentukan host mana yang memakai nama
        // route tanpa akhiran, jadi alamat resminya harus tetap di urutan pertama.
        $hosts = config('lajukasir.hosts');

        $this->assertSame('lajukasir.digipro-solution.com', $hosts[0]);
        $this->assertContains('lajukasir.localhost', $hosts);
        $this->assertSame($hosts, array_values(array_unique($hosts)));
    }

    public function test_the_content_from_the_admin_panel_is_rendered(): void
    {
        Feature::create([
            'icon' => 'heroicon-o-printer',
            'title' => 'Cetak struk Bluetooth',
            'description' => 'Struk langsung keluar dari printer thermal.',
            'order_column' => 0,
        ]);
        Package::create([
            'name' => 'Paket Toko',
            'price' => 'Rp 1.500.000',
            'price_unit' => 'sekali bayar',
            'badge' => 'TERLARIS',
            'features' => "Keranjang belanja\nLaporan harian",
            'is_highlighted' => true,
            'order_column' => 0,
        ]);
        Step::create([
            'icon' => 'heroicon-o-key',
            'title' => 'Terima lisensi',
            'description' => 'Impor berkas lisensi.',
            'order_column' => 0,
        ]);
        Screenshot::create(['title' => 'Halaman kasir', 'caption' => 'Tampilan utama.', 'order_column' => 0]);
        Faq::create(['question' => 'Butuh internet?', 'answer' => 'Tidak perlu.', 'order_column' => 0]);
        Testimonial::create(['name' => 'Bu Sri', 'store' => 'Warung Bu Sri', 'quote' => 'Catatannya jadi rapi.', 'order_column' => 0]);

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('Cetak struk Bluetooth', $html);
        $this->assertStringContainsString('Struk langsung keluar dari printer thermal.', $html);
        $this->assertStringContainsString('Rp 1.500.000', $html);
        $this->assertStringContainsString('TERLARIS', $html);
        // Keunggulan paket ditulis satu per baris, jadi keduanya terpisah.
        $this->assertStringContainsString('Keranjang belanja', $html);
        $this->assertStringContainsString('Laporan harian', $html);
        $this->assertStringContainsString('Terima lisensi', $html);
        $this->assertStringContainsString('Halaman kasir', $html);
        $this->assertStringContainsString('Butuh internet?', $html);
        $this->assertStringContainsString('Tidak perlu.', $html);
        $this->assertStringContainsString('Bu Sri', $html);
        $this->assertStringContainsString('Warung Bu Sri', $html);
        // Tanpa foto, kartunya memakai inisial nama pemiliknya.
        $this->assertStringContainsString('>BS<', $html);
    }

    public function test_sections_whose_content_is_still_empty_are_left_out(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        foreach (['fitur', 'paket', 'tampilan', 'cara', 'faq', 'testimoni', 'pasang'] as $section) {
            $this->assertStringNotContainsString('id="'.$section.'"', $html, 'Bagian '.$section.' seharusnya tidak dirender.');
        }

        // Bagian pembuka dan ajakan bertindak tetap ada karena keduanya punya
        // nilai bawaan.
        $this->assertStringContainsString('Kasir Toko yang Tetap Jalan', $html);
        $this->assertStringContainsString('Siap Membuat Catatan Toko Anda Lebih Rapi?', $html);
    }

    public function test_the_download_buttons_only_appear_when_they_have_a_link(): void
    {
        Setting::current()->update(['download_android_url' => 'https://play.google.com/store/apps/details?id=id.lajukasir']);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('id="pasang"', $html);
        $this->assertStringContainsString('https://play.google.com/store/apps/details?id=id.lajukasir', $html);
        $this->assertStringContainsString('Unduh untuk Android', $html);
        // Windows belum diisi, jadi tombolnya tidak ikut dirender.
        $this->assertStringNotContainsString('Unduh untuk Windows', $html);
    }

    public function test_the_admin_headings_replace_the_defaults(): void
    {
        Setting::current()->update([
            'features_heading' => 'Fitur Andalan LajuKasir',
            'hero_title' => 'Kasir Rapi untuk Warung Anda',
        ]);
        Setting::forgetCurrent();

        Feature::create(['title' => 'Fitur pertama', 'order_column' => 0]);

        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('Fitur Andalan LajuKasir', $html);
        $this->assertStringContainsString('Kasir Rapi untuk Warung Anda', $html);
        $this->assertStringNotContainsString('Kasir Toko yang Tetap Jalan Walau Internet Mati', $html);
    }

    public function test_the_page_uses_its_own_teal_stylesheet_and_the_shared_fonts(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        // Warna logonya dipakai di seluruh halaman: biru tua di bagian pembuka
        // dan kaki halaman, hijau teal di tombol serta pita penutup.
        $this->assertStringContainsString('from-ink-950', $html);
        $this->assertStringContainsString('from-brand-600', $html);
        $this->assertStringContainsString('bg-ink-950', $html);

        // Berkas gayanya sendiri, terpisah dari landing page DigiPro Solution,
        // supaya dua tema itu tidak saling memengaruhi.
        $cssUrl = Vite::asset('resources/css/lajukasir.css');

        $this->assertStringContainsString($cssUrl, $html);
        $this->assertStringNotContainsString(Vite::asset('resources/css/app.css'), $html);

        // Huruf Inter dimuat seperti di landing page DigiPro Solution, sedangkan
        // Instrument Sans disiapkan Vite untuk panel admin.
        $this->assertStringContainsString('family=Inter', $html);

        // Berkasnya dibaca dari disk, bukan dari jaringan, jadi alamatnya
        // dipotong sampai path-nya saja.
        $css = file_get_contents(public_path(ltrim((string) parse_url($cssUrl, PHP_URL_PATH), '/')));

        $this->assertIsString($css);

        // Dua warna logo LajuKasir — hijau teal dan biru tua — tersimpan apa
        // adanya sebagai tingkatan warna halamannya.
        $this->assertStringContainsString('--color-brand-600:#087b6a', $css);
        $this->assertStringContainsString('--color-ink-900:#1b2a3b', $css);
        $this->assertStringContainsString('Instrument Sans', $css);
    }

    public function test_the_header_shows_the_logo_in_its_original_colours(): void
    {
        $this->storeLogo();

        Setting::current()->update([
            'logo' => self::LOGO_FILE,
            // Warna adaptif dibiarkan menyala, tapi header tetap memakai warna
            // asli berkas logonya: biru tua dan hijau teal.
            'logo_adaptive' => true,
        ]);
        Setting::forgetCurrent();

        $html = $this->get($this->url())->assertOk()->getContent();

        [$navbar, $footer] = explode('</nav>', $html, 2);

        // Di header, logo tampil sebagai gambar apa adanya.
        $this->assertStringContainsString('src="/storage/'.self::LOGO_FILE.'"', $navbar);
        $this->assertStringNotContainsString('background-color: currentColor', $navbar);

        // Kaki halaman berlatar biru tua, jadi di sana logonya tetap digambar
        // sewarna teksnya supaya huruf "Kasir" yang biru tua tidak hilang.
        $this->assertStringContainsString('background-color: currentColor', $footer);
    }

    public function test_the_slogan_is_only_shown_in_the_footer(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        [$navbar, $footer] = explode('</nav>', $html, 2);

        // Ruang di header dipakai logonya saja, sedangkan slogan tetap tampil
        // di kaki halaman.
        $this->assertStringNotContainsString(Setting::DEFAULT_TAGLINE, $navbar);
        $this->assertStringContainsString(Setting::DEFAULT_TAGLINE, $footer);
    }

    public function test_the_preview_url_serves_the_same_page_on_the_main_domain(): void
    {
        $html = $this->get('/'.config('lajukasir.preview_path'))->assertOk()->getContent();

        $this->assertStringContainsString('Kasir Toko yang Tetap Jalan', $html);
        // Halaman kembar itu tidak boleh terindeks mesin pencari.
        $this->assertStringContainsString('noindex, nofollow', $html);
        // Kunjungan dari alamat pratinjau tidak dicatat di analitik.
        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_the_hero_panel_falls_back_to_a_gradient_frame_without_an_image(): void
    {
        $html = $this->get($this->url())->assertOk()->getContent();

        $this->assertStringContainsString('Unggah gambar aplikasi di menu Pengaturan LajuKasir', $html);
    }

    private function storeLogo(): void
    {
        Storage::disk('public')->put(self::LOGO_FILE, base64_decode(self::LOGO_PNG));
    }
}
