<?php

namespace Tests\Feature;

use App\Filament\Resources\HeroResource\Pages\EditHero;
use App\Models\Hero;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UploadedFileUrlTest extends TestCase
{
    use RefreshDatabase;

    private const FILE_NAME = 'uploaded-file-url-test.png';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::disk('public')->put(
            self::FILE_NAME,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
        );
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete(self::FILE_NAME);

        parent::tearDown();
    }

    /**
     * Filament hands the URL of an already uploaded file to the browser, where
     * FilePond fetches it to build the preview. That URL has to stay on the same
     * origin as the admin panel, otherwise the browser blocks the request and the
     * upload field hangs on "Loading" forever.
     */
    public function test_an_already_uploaded_file_is_exposed_to_the_browser_as_a_same_origin_url(): void
    {
        $this->actingAs(User::factory()->create());

        Filament::getPanel('admin');

        $hero = Hero::create([
            'title' => 'Test hero',
            'background_image' => self::FILE_NAME,
        ]);

        $files = Livewire::test(EditHero::class, ['record' => $hero->getKey()])
            ->instance()
            ->getFormUploadedFiles('data.background_image');

        $this->assertIsArray($files);

        $file = array_values($files)[0];

        $this->assertSame('/storage/'.self::FILE_NAME, $file['url']);
        $this->assertSame(self::FILE_NAME, $file['name']);
        $this->assertSame('image/png', $file['type']);
        $this->assertGreaterThan(0, $file['size']);
    }
}
