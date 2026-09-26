<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Situs';

    protected static ?string $modelLabel = 'Pengaturan Situs';

    protected static ?string $pluralModelLabel = 'Pengaturan Situs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('site_name')
                    ->label('Nama Situs')
                    ->maxLength(255)
                    ->helperText('Dipakai sebagai teks alternatif logo dan nama panel admin.'),
                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    // SiteSetting resolves the logo through the public disk, so
                    // the upload has to land there as well.
                    ->disk('public')
                    ->helperText('Format terbaik: PNG atau SVG dengan latar transparan.'),
                Forms\Components\Toggle::make('logo_adaptive')
                    ->label('Warna logo mengikuti background')
                    ->helperText('Logo digambar ulang memakai warna teks di sekitarnya: tampil gelap di header yang terang dan tampil putih di footer yang gelap. Perlu logo berlatar transparan.')
                    ->default(true),
                Forms\Components\Placeholder::make('logo_preview')
                    ->label('Pratinjau logo')
                    ->content(fn (Get $get): View => static::logoPreview($get))
                    ->columnSpanFull(),
                Forms\Components\Section::make('Tombol WhatsApp')
                    ->description('Tombol mengambang di kanan bawah landing page supaya pengunjung bisa menghubungi admin hanya dengan sekali klik.')
                    ->schema([
                        Forms\Components\Toggle::make('whatsapp_enabled')
                            ->label('Tampilkan tombol WhatsApp mengambang')
                            ->helperText('Tombol "Hubungi Kami" di header tetap memakai nomor di bawah, terlepas dari pilihan ini.')
                            ->default(true),
                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(32)
                            ->placeholder(SiteSetting::DEFAULT_WHATSAPP_NUMBER)
                            ->regex('/^\+?[0-9\s().-]+$/')
                            ->validationMessages([
                                'regex' => 'Nomor hanya boleh berisi angka, spasi, tanda +, tanda -, titik, atau tanda kurung.',
                            ])
                            ->helperText('Boleh ditulis 0896..., +62 896... atau 62896.... Nomor ini juga dipakai tombol "Hubungi Kami" di header.'),
                        Forms\Components\TextInput::make('whatsapp_greeting')
                            ->label('Sapaan di balon obrolan')
                            ->maxLength(255)
                            ->placeholder(SiteSetting::DEFAULT_WHATSAPP_GREETING),
                        Forms\Components\TextInput::make('whatsapp_message')
                            ->label('Pesan otomatis')
                            ->maxLength(255)
                            ->placeholder(SiteSetting::DEFAULT_WHATSAPP_MESSAGE)
                            ->helperText('Teks ini sudah terisi di kolom pesan WhatsApp saat pengunjung menekan tombolnya.'),
                    ]),
                Forms\Components\Section::make('SEO & Mesin Pencari')
                    ->description('Judul, deskripsi dan pratinjau tautan yang dilihat Google, WhatsApp dan media sosial.')
                    ->schema([
                        Forms\Components\TextInput::make('site_url')
                            ->label('Alamat situs')
                            ->url()
                            ->maxLength(255)
                            ->placeholder(config('seo.url'))
                            ->helperText('Alamat resmi situs, misalnya https://digipro-solution.com. Dipakai di tag canonical, sitemap.xml dan robots.txt. Kosong berarti memakai SITE_URL dari berkas .env.'),
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Judul halaman (meta title)')
                            ->maxLength(70)
                            ->placeholder(fn (): string => SiteSetting::current()->siteName().' - '.SiteSetting::DEFAULT_META_TITLE_SUFFIX)
                            ->helperText('Idealnya 50-60 karakter. Kosong berarti nama situs ditambah akhiran "'.SiteSetting::DEFAULT_META_TITLE_SUFFIX.'".'),
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Deskripsi halaman (meta description)')
                            ->rows(3)
                            ->maxLength(320)
                            ->placeholder(SiteSetting::DEFAULT_META_DESCRIPTION)
                            ->helperText('Idealnya 120-160 karakter. Tampil di bawah judul pada hasil pencarian dan saat tautannya dibagikan.'),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Kata kunci')
                            ->maxLength(255)
                            ->placeholder(SiteSetting::DEFAULT_META_KEYWORDS)
                            ->helperText('Pisahkan dengan koma, misalnya: jasa pembuatan website, website umkm, toko online.'),
                        Forms\Components\FileUpload::make('og_image')
                            ->label('Gambar pratinjau (og:image)')
                            ->image()
                            // Sama seperti logo: SiteSetting menyajikan gambar ini
                            // dari disk publik.
                            ->disk('public')
                            ->helperText('Tampil saat tautan situs dibagikan di WhatsApp, Facebook atau LinkedIn. Ukuran yang disarankan 1200 x 630 piksel.'),
                        Forms\Components\Toggle::make('robots_index')
                            ->label('Izinkan mesin pencari mengindeks situs')
                            ->helperText('Matikan selama situsnya masih disiapkan: robots.txt akan menutup seluruh situs dan halamannya diberi penanda noindex.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        // Site settings are a singleton: only the existing record is edited.
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditSiteSetting::route('/'),
        ];
    }

    /**
     * Preview of the logo as it will appear on the landing page header, in the
     * landing page footer and in the admin panel.
     */
    protected static function logoPreview(Get $get): View
    {
        $state = $get('logo');
        $path = is_array($state) ? collect($state)->first() : $state;

        // The state only points at a stored file once the form has been saved.
        $isStored = is_string($path) && filled($path) && Storage::disk('public')->exists($path);
        $storedPath = $isStored ? (string) $path : null;

        return view('filament.forms.logo-preview', [
            'logoUrl' => $storedPath ? Storage::disk('public')->url($storedPath) : null,
            'adaptive' => (bool) $get('logo_adaptive'),
            'name' => (string) ($get('site_name') ?: config('app.name')),
            'ratio' => SiteSetting::aspectRatioFor($storedPath),
        ]);
    }
}
