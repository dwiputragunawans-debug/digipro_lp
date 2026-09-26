<?php

namespace App\Providers\Filament;

use App\Models\SiteSetting;
use App\Rules\SafeSvgUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(fn (): string => SiteSetting::current()->siteName())
            ->brandLogo(fn (): ?View => SiteSetting::current()->hasLogo()
                ? view('components.site-logo', [
                    'height' => '100%',
                    'attributes' => new ComponentAttributeBag(['class' => 'text-gray-950 dark:text-white']),
                ])
                : null)
            ->brandLogoHeight('2rem')
            ->favicon(fn (): ?string => SiteSetting::current()->logoUrl())
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4rem')
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                // White, compact sidebar, see resources/views/filament/panel-styles.blade.php.
                fn (): View => view('filament.panel-styles'),
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                // Pemeriksaan token CSRF untuk form dan unggahan di panel.
                // PreventRequestForgery adalah nama baru dari VerifyCsrfToken,
                // jadi daftar ini tetap sama isinya dengan bawaan Filament.
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * Aturan yang berlaku untuk semua kolom unggah berkas di panel ini.
     *
     * Dua hal yang dijaga di sini:
     *
     * 1. Ukuran berkas dibatasi (MAX_UPLOAD_SIZE), supaya sebuah berkas raksasa
     *    tidak bisa memenuhi ruang penyimpanan hosting. Tanpa batas ini Filament
     *    menerima berkas sebesar apa pun.
     * 2. Isi berkas SVG diperiksa. SVG bisa memuat <script> yang berjalan di
     *    browser pengunjung begitu tautannya dibuka langsung dari /storage/…,
     *    jadi berkas seperti itu ditolak sebelum tersimpan.
     *
     * Aturan ini dipasang di sini, bukan satu per satu di setiap menu, supaya
     * menu yang ditambahkan nanti otomatis ikut terlindungi.
     */
    public function boot(): void
    {
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload
                ->maxSize((int) config('security.max_upload_size', 8192))
                ->rules([new SafeSvgUpload]);
        });
    }
}
