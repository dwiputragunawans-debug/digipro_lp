<?php

namespace Tests\Feature;

use App\Filament\Resources\FeatureResource;
use App\Filament\Resources\FeatureResource\Pages\EditFeature;
use App\Models\Feature;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureIconTest extends TestCase
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

    public function test_each_feature_card_uses_the_icon_stored_in_the_database(): void
    {
        $this->createFeature('heroicon-o-rocket-launch', 'Cepat');
        $this->createFeature('heroicon-o-device-phone-mobile', 'Mobile friendly');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($this->iconPath('o-rocket-launch'), false);
        $response->assertSee($this->iconPath('o-device-phone-mobile'), false);

        // Ikon petir hanya dipakai kalau kolomnya kosong atau tidak dikenal.
        $response->assertDontSee($this->iconPath('o-bolt'), false);
    }

    public function test_a_feature_without_a_usable_icon_falls_back_to_the_bolt(): void
    {
        $this->createFeature(null, 'Tanpa ikon');
        $this->createFeature('ikon-lama', 'Ikon lama');

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('ikon-lama', false);

        // Once for each of the two features.
        $this->assertSame(2, substr_count($response->getContent(), $this->iconPath('o-bolt')));
    }

    public function test_the_admin_form_offers_the_icons_and_saves_the_chosen_one(): void
    {
        $this->actingAs(User::factory()->create());

        $feature = $this->createFeature('ikon-lama', 'Fitur lama');

        Livewire::test(EditFeature::class, ['record' => $feature->getKey()])
            // Nilai lama yang tidak ada di daftar tetap muncul supaya tidak hilang.
            ->assertSee('ikon-lama (tidak ada di daftar', false)
            ->fillForm(['icon' => 'heroicon-o-rocket-launch'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(FeatureResource::getUrl('index'));

        $this->assertSame('heroicon-o-rocket-launch', $feature->refresh()->icon);
    }

    private function createFeature(?string $icon, string $title): Feature
    {
        return Feature::create([
            'icon' => $icon,
            'title' => $title,
            'description' => 'Deskripsi '.$title.'.',
            'order_column' => 0,
        ]);
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
