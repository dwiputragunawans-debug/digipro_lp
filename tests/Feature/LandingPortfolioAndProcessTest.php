<?php

namespace Tests\Feature;

use App\Filament\Resources\ProcessResource;
use App\Filament\Resources\ProcessResource\Pages\EditProcess;
use App\Models\Portfolio;
use App\Models\Process;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class LandingPortfolioAndProcessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Same one pixel PNG that the upload tests use.
     */
    private const IMAGE_NAME = 'landing-portfolio-test.png';

    protected function setUp(): void
    {
        parent::setUp();

        // App\Models\User does not implement FilamentUser, and Filament only lets
        // such users into a panel while the app environment is "local". Building a
        // resource URL needs the panel to be the current one.
        config(['app.env' => 'local']);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Storage::disk('public')->put(
            self::IMAGE_NAME,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
        );
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete(self::IMAGE_NAME);

        parent::tearDown();
    }

    public function test_the_portfolio_cards_are_taken_from_the_portfolios_table(): void
    {
        Portfolio::create([
            'image' => self::IMAGE_NAME,
            'title' => 'Toko Kelontong',
            'link' => 'https://example.test/kelontong',
            'order_column' => 0,
        ]);

        $section = $this->sectionOf($this->get('/'), 'karya');

        $this->assertStringContainsString('/storage/'.self::IMAGE_NAME, $section);
        $this->assertStringContainsString('Toko Kelontong', $section);
        $this->assertStringContainsString('https://example.test/kelontong', $section);

        // Tautan karya dibuka di tab baru, jadi halaman ini tidak ikut tertinggal.
        $this->assertStringContainsString('<a href="https://example.test/kelontong" target="_blank" rel="noopener"', $section);

        // Kotak contoh hanya dipakai kalau gambar atau datanya tidak ada.
        $this->assertStringNotContainsString('Project Mockup', $section);
    }

    public function test_an_image_that_is_gone_from_the_disk_shows_the_placeholder(): void
    {
        Portfolio::create([
            'image' => 'gambar-hilang.png',
            'title' => 'Gambar Hilang',
            'order_column' => 0,
        ]);

        $section = $this->sectionOf($this->get('/'), 'karya');

        $this->assertStringContainsString('Project Mockup', $section);
        $this->assertStringNotContainsString('gambar-hilang.png', $section);
        // Judulnya tetap tampil, jadi kartunya tidak kosong melompong.
        $this->assertStringContainsString('Gambar Hilang', $section);
    }

    public function test_the_portfolio_section_falls_back_to_the_placeholders_without_portfolios(): void
    {
        $section = $this->sectionOf($this->get('/'), 'karya');

        $this->assertSame(4, substr_count($section, 'Project Mockup'));
    }

    public function test_the_process_steps_are_taken_from_the_processes_table(): void
    {
        Process::create([
            'icon' => null,
            'title' => 'Konsultasi',
            'description' => 'Ngobrol dulu soal kebutuhan Anda.',
            'order_column' => 0,
        ]);

        Process::create([
            'icon' => 'heroicon-o-clipboard-document-check',
            'title' => 'Pembuatan',
            'description' => 'Kami kerjakan websitenya.',
            'order_column' => 1,
        ]);

        $section = $this->sectionOf($this->get('/'), 'proses');

        $this->assertStringContainsString('Konsultasi', $section);
        $this->assertStringContainsString('Ngobrol dulu soal kebutuhan Anda.', $section);
        $this->assertStringContainsString('Pembuatan', $section);
        $this->assertStringContainsString('Kami kerjakan websitenya.', $section);

        // Langkah tanpa ikon menampilkan nomornya, langkah yang punya ikon tidak.
        $this->assertStringContainsString('>1</span>', $section);
        $this->assertStringNotContainsString('>2</span>', $section);
        $this->assertStringContainsString($this->iconPath('o-clipboard-document-check'), $section);

        // Langkah contoh tidak dipakai kalau tabelnya tidak kosong.
        $this->assertStringNotContainsString('Online', $section);
    }

    public function test_an_unknown_process_icon_falls_back_to_the_step_number(): void
    {
        Process::create([
            'icon' => 'icons1',
            'title' => 'Pengujian',
            'description' => 'Dicek dulu sebelum online.',
            'order_column' => 0,
        ]);

        $section = $this->sectionOf($this->get('/'), 'proses');

        $this->assertStringContainsString('>1</span>', $section);
        // Ikon cadangan (petir) tidak dipakai di bagian ini.
        $this->assertStringNotContainsString($this->iconPath('o-bolt'), $section);
    }

    public function test_the_process_section_falls_back_to_the_demo_steps_without_processes(): void
    {
        $section = $this->sectionOf($this->get('/'), 'proses');

        $this->assertStringContainsString('Konsultasi', $section);
        $this->assertStringContainsString('Pembuatan', $section);
        $this->assertStringContainsString('Pengujian', $section);
        $this->assertStringContainsString('Online', $section);
        $this->assertStringContainsString('>4</span>', $section);
    }

    public function test_five_process_steps_are_laid_out_in_a_single_row(): void
    {
        foreach (range(0, 4) as $order) {
            Process::create([
                'icon' => null,
                'title' => 'Langkah '.($order + 1),
                'description' => 'Deskripsi langkah '.($order + 1).'.',
                'order_column' => $order,
            ]);
        }

        $section = $this->sectionOf($this->get('/'), 'proses');

        // Lima langkah = lima kolom, jadi tidak ada langkah yang turun ke baris kedua.
        $this->assertStringContainsString('lg:grid-cols-5', $section);
        $this->assertStringNotContainsString('md:grid-cols-4', $section);

        // Kelima langkah benar-benar dirender beserta nomornya (ikonnya kosong).
        $this->assertSame(5, substr_count($section, 'text-2xl font-bold'));

        // Garis penghubung menyala bersamaan dengan barisnya yang utuh.
        $this->assertStringContainsString('hidden lg:block absolute top-12', $section);
    }

    public function test_up_to_four_process_steps_keep_the_original_grid(): void
    {
        foreach (range(0, 3) as $order) {
            Process::create([
                'icon' => null,
                'title' => 'Langkah '.($order + 1),
                'description' => 'Deskripsi langkah '.($order + 1).'.',
                'order_column' => $order,
            ]);
        }

        $section = $this->sectionOf($this->get('/'), 'proses');

        $this->assertStringContainsString('md:grid-cols-4', $section);
        $this->assertStringNotContainsString('lg:grid-cols-5', $section);
        $this->assertStringContainsString('hidden md:block absolute top-12', $section);
    }

    public function test_the_admin_form_saves_the_steps_icon_title_and_description(): void
    {
        $this->actingAs(User::factory()->create());

        $process = Process::create([
            'icon' => 'icons1',
            'title' => 'Langkah lama',
            'description' => 'Deskripsi lama.',
            'order_column' => 0,
        ]);

        Livewire::test(EditProcess::class, ['record' => $process->getKey()])
            // Nilai lama yang tidak ada di daftar tetap muncul supaya tidak hilang.
            ->assertSee('icons1 (tidak ada di daftar', false)
            ->fillForm([
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'title' => 'Konsultasi Awal',
                'description' => 'Ngobrol dulu soal kebutuhan Anda.',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ProcessResource::getUrl('index'));

        $process->refresh();

        $this->assertSame('heroicon-o-chat-bubble-left-right', $process->icon);
        $this->assertSame('Konsultasi Awal', $process->title);
        $this->assertSame('Ngobrol dulu soal kebutuhan Anda.', $process->description);
    }

    public function test_the_process_model_only_resolves_a_usable_step_icon(): void
    {
        $this->assertSame('heroicon-o-chat-bubble-left-right', (new Process(['icon' => 'ChatBubbleLeftRight']))->step_icon);
        $this->assertNull((new Process(['icon' => 'icons1']))->step_icon);
        $this->assertNull((new Process(['icon' => null]))->step_icon);
    }

    public function test_the_portfolio_model_only_exposes_an_image_that_is_there(): void
    {
        $this->assertSame('/storage/'.self::IMAGE_NAME, (new Portfolio(['image' => self::IMAGE_NAME]))->imageUrl());
        $this->assertNull((new Portfolio(['image' => 'gambar-hilang.png']))->imageUrl());
        $this->assertNull((new Portfolio(['image' => null]))->imageUrl());
    }

    private function sectionOf(TestResponse $response, string $id): string
    {
        $response->assertOk();

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
