<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminPanelLocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The panel is only served to users in the local environment, because
     * App\Models\User does not implement FilamentUser, see Filament\Http\Middleware\Authenticate.
     */
    private function enterPanel(): void
    {
        config(['app.env' => 'local']);

        $this->actingAs(User::factory()->create());
    }

    public function test_the_application_runs_in_indonesian(): void
    {
        $this->assertSame('id', app()->getLocale());
        $this->assertSame('id', Carbon::getLocale());
    }

    public function test_validation_messages_are_written_in_indonesian(): void
    {
        $this->assertSame('Kolom judul wajib diisi.', trans('validation.required', ['attribute' => trans('validation.attributes.title')]));
        $this->assertSame('Email dan kata sandi tidak cocok dengan data kami.', trans('auth.failed'));
    }

    public function test_the_login_screen_is_written_in_indonesian(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk ke akun Anda')
            ->assertSee('Alamat email')
            ->assertSee('Kata sandi')
            ->assertSee('Ingat saya');
    }

    public function test_the_sidebar_navigation_uses_indonesian_labels_and_matching_icons(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $groups = [];

        foreach (Filament::getCurrentPanel()->getNavigation() as $group) {
            // Menu landing page DigiPro Solution tidak diberi judul grup, jadi
            // namanya kosong.
            $name = (string) ($group->getLabel() ?? '');

            foreach ($group->getItems() as $item) {
                $groups[$name][$item->getLabel()] = $item->getIcon();
            }
        }

        $this->assertSame([
            'Dasbor' => 'heroicon-o-home',
            'Bagian CTA' => 'heroicon-o-megaphone',
            'Fitur' => 'heroicon-o-sparkles',
            'Banner Hero' => 'heroicon-o-photo',
            'Portofolio' => 'heroicon-o-briefcase',
            'Proses' => 'heroicon-o-numbered-list',
            'Layanan' => 'heroicon-o-wrench-screwdriver',
            'Pengaturan Situs' => 'heroicon-o-cog-6-tooth',
        ], $groups['']);

        // Isi halaman LajuKasir dikumpulkan di grupnya sendiri, supaya editor
        // tidak tertukar dengan menu landing page DigiPro Solution.
        $this->assertSame([
            'Pengaturan LajuKasir' => 'heroicon-o-adjustments-horizontal',
            'Fitur Unggulan' => 'heroicon-o-rocket-launch',
            'Paket Harga' => 'heroicon-o-banknotes',
            'Cara Mulai' => 'heroicon-o-list-bullet',
            'Tampilan Aplikasi' => 'heroicon-o-device-phone-mobile',
            'Pertanyaan Umum' => 'heroicon-o-question-mark-circle',
            'Testimoni' => 'heroicon-o-chat-bubble-left-right',
            'Analitik LajuKasir' => 'heroicon-o-chart-bar',
        ], $groups['LajuKasir']);
    }

    public function test_the_sidebar_is_white_and_compact(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            // The panel provider sizes the column down and the style hook makes
            // the background white, see resources/views/filament/panel-styles.blade.php.
            ->assertSee('--sidebar-width: 16rem', false)
            ->assertSee('.fi-sidebar {', false)
            ->assertSee('background-color: #ffffff !important;', false)
            ->assertSee('font-size: 0.8125rem;', false);
    }

    public function test_the_resource_pages_are_written_in_indonesian(): void
    {
        $this->enterPanel();

        // Filament only renders the table, and with it the column headers, once
        // there is at least one record to show.
        Feature::create([
            'icon' => 'heroicon-o-bolt',
            'title' => 'Desain mobile friendly',
            'description' => 'Tampilan yang rapi di semua ukuran layar.',
            'order_column' => 0,
        ]);

        $this->get('/admin/features')
            ->assertOk()
            ->assertSee('Ikon')
            ->assertSee('Judul')
            ->assertSee('Urutan')
            ->assertSee('Buat Fitur', false);

        $this->get('/admin/site-settings')
            ->assertOk()
            ->assertSee('Nama Situs', false)
            ->assertSee('Warna logo mengikuti background', false);
    }
}
