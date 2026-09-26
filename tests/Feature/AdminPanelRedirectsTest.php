<?php

namespace Tests\Feature;

use App\Filament\Resources\CtaSectionResource;
use App\Filament\Resources\FeatureResource;
use App\Filament\Resources\HeroResource;
use App\Filament\Resources\PortfolioResource;
use App\Filament\Resources\ProcessResource;
use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\SiteSettingResource;
use App\Filament\Resources\SiteSettingResource\Pages\EditSiteSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelRedirectsTest extends TestCase
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

        // The settings row is remembered for the duration of a request, and one
        // PHP process runs every test.
        SiteSetting::forgetCurrent();

        $this->actingAs(User::factory()->create());
    }

    protected function tearDown(): void
    {
        SiteSetting::forgetCurrent();

        parent::tearDown();
    }

    /**
     * Every menu that has a list page, with the smallest amount of form data it
     * accepts. The list page, the create page, the edit page and the model are
     * read from the resource itself.
     *
     * @return array<string, array{0: class-string, 1: array<string, mixed>}>
     */
    public static function menus(): array
    {
        return [
            'Bagian CTA' => [CtaSectionResource::class, ['title' => 'Bagian CTA baru']],
            'Fitur' => [FeatureResource::class, [
                'icon' => 'heroicon-o-bolt',
                'title' => 'Fitur baru',
                'description' => 'Deskripsi fitur baru.',
                'order_column' => 0,
            ]],
            'Banner Hero' => [HeroResource::class, ['title' => 'Banner hero baru']],
            'Portofolio' => [PortfolioResource::class, ['title' => 'Portofolio baru', 'order_column' => 0]],
            'Proses' => [ProcessResource::class, [
                'title' => 'Proses baru',
                'description' => 'Deskripsi proses baru.',
                'order_column' => 0,
            ]],
            'Layanan' => [ServiceResource::class, [
                'title' => 'Layanan baru',
                'description' => 'Deskripsi layanan baru.',
                'order_column' => 0,
            ]],
        ];
    }

    #[DataProvider('menus')]
    public function test_saving_a_new_record_lands_on_the_list_page(string $resource, array $data): void
    {
        $model = $resource::getModel();

        Livewire::test($resource::getPages()['create']->getPage())
            ->fillForm($data)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect($resource::getUrl('index'));

        // The record is stored before the redirect to the list happens.
        $this->assertSame(1, $model::query()->count());
    }

    #[DataProvider('menus')]
    public function test_saving_an_edited_record_lands_on_the_list_page(string $resource, array $data): void
    {
        $record = $resource::getModel()::create($data);

        Livewire::test($resource::getPages()['edit']->getPage(), ['record' => $record->getKey()])
            ->fillForm(['title' => 'Judul yang diubah'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect($resource::getUrl('index'));

        // The change is stored before the redirect to the list happens.
        $this->assertSame('Judul yang diubah', $record->refresh()->title);
    }

    #[DataProvider('menus')]
    public function test_deleting_a_record_lands_on_the_list_page(string $resource, array $data): void
    {
        $record = $resource::getModel()::create($data);

        Livewire::test($resource::getPages()['edit']->getPage(), ['record' => $record->getKey()])
            ->callAction(DeleteAction::class)
            ->assertRedirect($resource::getUrl('index'));

        $this->assertDatabaseMissing($record->getTable(), [$record->getKeyName() => $record->getKey()]);
    }

    public function test_saving_the_site_settings_lands_on_the_settings_screen(): void
    {
        // Site settings are a singleton, so the settings screen is its own list
        // page: saving redirects to the screen that was just saved.
        Livewire::test(EditSiteSetting::class)
            ->fillForm(['site_name' => 'DigiPro Studio'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(SiteSettingResource::getUrl('index'));

        $this->assertSame('DigiPro Studio', SiteSetting::current()->site_name);
    }
}
