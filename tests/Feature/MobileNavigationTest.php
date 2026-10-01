<?php

namespace Tests\Feature;

use App\Models\Lajukasir\Feature;
use App\Models\Lajukasir\Setting;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menu navigasi untuk ponsel dan tablet.
 *
 * Menu mendatarnya baru tampil di layar lebar; di bawah itu digantikan tombol
 * menu yang membuka daftar menu bertumpuk. Isinya sama, jadi tidak ada bagian
 * halaman yang hanya bisa dijangkau dari layar lebar.
 */
class MobileNavigationTest extends TestCase
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

    public function test_the_digipro_header_has_a_menu_button_for_small_screens(): void
    {
        $nav = $this->navOf($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('data-nav-toggle', $nav);
        $this->assertStringContainsString('aria-expanded="false"', $nav);
        $this->assertStringContainsString('aria-controls="primary-navigation"', $nav);

        // Tombolnya muncul di ponsel dan tablet, lalu disembunyikan di layar
        // lebar (lg) tempat menunya sudah tampil mendatar.
        $this->assertStringContainsString('lg:hidden', $nav);
        $this->assertStringContainsString('hidden lg:flex items-center space-x-8', $nav);
    }

    public function test_the_digipro_menu_panel_lists_the_same_sections_as_the_desktop_menu(): void
    {
        $nav = $this->navOf($this->get('/')->assertOk()->getContent());

        $this->assertStringContainsString('id="primary-navigation" data-nav-panel', $nav);

        // Setiap menu dipakai dua kali: mendatar di layar lebar dan bertumpuk di
        // dalam panel ponsel.
        foreach (['#mengapa', '#solusi', '#karya', '#proses'] as $target) {
            $this->assertSame(2, substr_count($nav, 'href="'.$target.'"'), 'Menu '.$target.' seharusnya ada dua kali.');
        }

        // Termasuk tombol WhatsApp-nya, di kedua daftar, dan keduanya di tab baru.
        $this->assertSame(2, substr_count($nav, 'data-whatsapp-click target="_blank" rel="noopener"'));
    }

    public function test_the_lajukasir_header_has_the_same_menu_button(): void
    {
        $nav = $this->navOf($this->get('http://'.self::HOST.'/')->assertOk()->getContent());

        $this->assertStringContainsString('data-nav-toggle', $nav);
        $this->assertStringContainsString('aria-controls="primary-navigation"', $nav);
        $this->assertStringContainsString('id="primary-navigation" data-nav-panel', $nav);
        $this->assertStringContainsString('lg:hidden', $nav);
    }

    public function test_the_lajukasir_menu_only_lists_the_sections_that_have_content(): void
    {
        Feature::create(['title' => 'Fitur pertama', 'order_column' => 0]);

        $nav = $this->navOf($this->get('http://'.self::HOST.'/')->assertOk()->getContent());

        // Bagian Fitur sudah ada isinya, jadi menunya muncul di kedua daftar.
        $this->assertSame(2, substr_count($nav, 'href="#fitur"'));

        // Bagian lain masih kosong, jadi tidak ada menu yang menggantung.
        foreach (['#paket', '#tampilan', '#cara', '#faq'] as $target) {
            $this->assertStringNotContainsString('href="'.$target.'"', $nav);
        }
    }

    /**
     * Isi elemen header, supaya asersinya tidak menangkap kata yang sama dari
     * bagian lain di halaman.
     */
    private function navOf(string $html): string
    {
        $start = strpos($html, '<nav data-nav');

        $this->assertNotFalse($start, 'Header dengan menu navigasinya tidak ditemukan.');

        return substr($html, $start, strpos($html, '</nav>', $start) - $start);
    }
}
