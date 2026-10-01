<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use App\Support\Heroicons;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class LandingServiceSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // App\Models\User does not implement FilamentUser, and Filament only lets
        // such users into a panel while the app environment is "local". Building a
        // resource URL needs the panel to be the current one.
        config(['app.env' => 'local']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_the_solution_cards_are_taken_from_the_services_table(): void
    {
        Service::create([
            'icon' => 'heroicon-o-building-storefront',
            'title' => 'Toko Online',
            'description' => 'Jualan lewat internet.',
            'badge' => 'POPULER',
            'is_highlighted' => false,
            'benefits' => "Sistem Keranjang Belanja\n\n- Manajemen Produk Mudah\n",
            'cta_text' => 'Lihat Paket',
            'cta_url' => 'https://example.test/toko',
            'order_column' => 0,
        ]);

        $section = $this->solutionSection();

        $this->assertStringContainsString('Toko Online', $section);
        $this->assertStringContainsString('Jualan lewat internet.', $section);
        $this->assertStringContainsString('POPULER', $section);
        $this->assertStringContainsString('Lihat Paket', $section);
        $this->assertStringContainsString('https://example.test/toko', $section);
        $this->assertStringContainsString($this->iconPath('o-building-storefront'), $section);

        // Baris kosong dibuang dan penanda daftar "-" di awal baris dihilangkan.
        $this->assertStringContainsString('Sistem Keranjang Belanja', $section);
        $this->assertStringContainsString('Manajemen Produk Mudah', $section);
        $this->assertStringNotContainsString('- Manajemen Produk Mudah', $section);

        // Kartu contoh hanya dipakai kalau tabel Layanan masih kosong.
        $this->assertStringNotContainsString('Katalog', $section);
        $this->assertStringContainsString('bg-slate-100', $section);
    }

    public function test_a_service_marked_as_highlighted_is_drawn_as_the_dark_card(): void
    {
        Service::create([
            'icon' => 'heroicon-o-code-bracket',
            'title' => 'Aplikasi Web Custom',
            'description' => 'Sistem khusus.',
            'is_highlighted' => true,
            'benefits' => 'Sistem Kasir (POS) Berbasis Web',
            'order_column' => 1,
        ]);

        Service::create([
            'icon' => 'heroicon-o-briefcase',
            'title' => 'Website Profil',
            'description' => 'Website sederhana.',
            'is_highlighted' => false,
            'benefits' => 'Desain Modern',
            'order_column' => 2,
        ]);

        $section = $this->solutionSection();

        $this->assertStringContainsString('bg-slate-900', $section);
        // Tombol kartu utama dan kartu biasa punya tulisan bawaan yang berbeda.
        $this->assertStringContainsString('>Hubungi Kami</a>', $section);
        $this->assertStringContainsString('>Selengkapnya</a>', $section);
        // Tanpa label sudut, pitanya tidak digambar sama sekali.
        $this->assertStringNotContainsString('rounded-bl-lg', $section);
    }

    public function test_the_solution_section_falls_back_to_the_demo_cards_when_there_are_no_services(): void
    {
        $section = $this->solutionSection();

        $this->assertStringContainsString('Website Profil Bisnis', $section);
        $this->assertStringContainsString('Katalog &amp; Toko Online', $section);
        $this->assertStringContainsString('Aplikasi Web Custom', $section);
        $this->assertStringContainsString('POPULER', $section);
        $this->assertStringContainsString('rounded-bl-lg', $section);
        $this->assertStringContainsString($this->iconPath('o-briefcase'), $section);
    }

    public function test_the_admin_form_saves_the_icon_and_the_card_options(): void
    {
        $this->actingAs(User::factory()->create());

        $service = Service::create([
            'icon' => 'icons1',
            'title' => 'Layanan lama',
            'description' => 'Deskripsi lama.',
            'benefits' => 'Keunggulan lama',
            'order_column' => 0,
        ]);

        Livewire::test(EditService::class, ['record' => $service->getKey()])
            // Nilai lama yang tidak ada di daftar tetap muncul supaya tidak hilang.
            ->assertSee('icons1 (tidak ada di daftar', false)
            ->fillForm([
                'icon' => 'heroicon-o-rocket-launch',
                'title' => 'Paket Baru',
                'description' => 'Deskripsi baru.',
                'badge' => 'BARU',
                'is_highlighted' => true,
                'benefits' => "Satu\nDua",
                'cta_text' => 'Pesan Sekarang',
                'cta_url' => 'https://example.test/pesan',
                'whatsapp_message' => 'Halo, saya mau tanya paket {paket}.',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ServiceResource::getUrl('index'));

        $service->refresh();

        $this->assertSame('heroicon-o-rocket-launch', $service->icon);
        $this->assertSame('Paket Baru', $service->title);
        $this->assertSame('BARU', $service->badge);
        $this->assertTrue($service->is_highlighted);
        $this->assertSame("Satu\nDua", $service->benefits);
        $this->assertSame('Pesan Sekarang', $service->cta_text);
        $this->assertSame('https://example.test/pesan', $service->cta_url);
        // Template pesan WhatsApp disimpan apa adanya; penanda {paket} baru
        // diganti saat halamannya dibuka.
        $this->assertSame('Halo, saya mau tanya paket {paket}.', $service->whatsapp_message);
    }

    public function test_the_service_model_normalizes_the_icon_and_splits_the_benefits(): void
    {
        $service = new Service([
            'icon' => 'icons1',
            'benefits' => "  • Satu \n\n- Dua\n   \n",
        ]);

        $this->assertSame(Heroicons::FALLBACK, $service->icon_name);
        $this->assertSame(['Satu', 'Dua'], $service->benefit_list);
    }

    /**
     * The markup of the "Solusi Digital untuk UMKM" section, so the assertions do
     * not pick up the same words from the footer or the other sections.
     */
    private function solutionSection(): string
    {
        $response = $this->get('/');

        $response->assertOk();

        return $this->sectionOf($response, 'solusi');
    }

    private function sectionOf(TestResponse $response, string $id): string
    {
        $html = $response->getContent();

        $start = strpos($html, '<section id="'.$id.'"');

        $this->assertNotFalse($start, "Bagian {$id} tidak ada di halaman depan.");

        return substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    /**
     * The path of the Heroicons SVG that should end up on the landing page.
     */
    private function iconPath(string $icon): string
    {
        $file = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg/'.$icon.'.svg');

        preg_match('/d="([^"]+)"/', file_get_contents($file), $matches);

        return $matches[1];
    }
}
