<?php

namespace App\Filament\Resources\Lajukasir;

use App\Filament\Resources\Lajukasir\SettingResource\Pages;
use App\Models\Lajukasir\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Isi halaman LajuKasir.
 *
 * Satu-satunya baris di tabelnya diubah dari satu layar bertab, supaya admin
 * menemukan semua tulisan halaman di satu tempat: merek, bagian pembuka, judul
 * setiap bagian, tautan unduhan, ajakan bertindak, WhatsApp dan SEO.
 *
 * Menu ini berada di grup navigasi "LajuKasir" supaya tidak tercampur dengan
 * menu landing page DigiPro Solution.
 */
class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $slug = 'lajukasir/pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Pengaturan LajuKasir';

    protected static ?string $modelLabel = 'Pengaturan LajuKasir';

    protected static ?string $pluralModelLabel = 'Pengaturan LajuKasir';

    protected static ?string $navigationGroup = 'LajuKasir';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Pengaturan LajuKasir')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Merek')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Forms\Components\TextInput::make('site_name')
                                    ->label('Nama Situs')
                                    ->maxLength(255)
                                    ->placeholder(Setting::DEFAULT_SITE_NAME)
                                    ->helperText('Dipakai sebagai teks alternatif logo, nama di tab browser dan judul bawaan SEO.'),
                                Forms\Components\TextInput::make('site_url')
                                    ->label('Alamat Situs')
                                    ->maxLength(255)
                                    ->placeholder((string) config('lajukasir.url'))
                                    ->helperText('Alamat subdomain halaman ini, dipakai untuk tag canonical, og:url, sitemap.xml dan robots.txt. Kosongkan untuk memakai alamat di berkas .env.'),
                                Forms\Components\TextInput::make('brand_tagline')
                                    ->label('Slogan')
                                    ->maxLength(255)
                                    ->placeholder(Setting::DEFAULT_TAGLINE)
                                    ->helperText('Tulisan pendek di bawah logo, misalnya "Aplikasi Kasir Toko".'),
                                Forms\Components\FileUpload::make('logo')
                                    ->label('Logo')
                                    ->image()
                                    // Setting resolves the logo through the public disk,
                                    // so the upload has to land there as well.
                                    ->disk('public')
                                    ->helperText('Format terbaik: PNG atau SVG dengan latar transparan.'),
                                Forms\Components\Toggle::make('logo_adaptive')
                                    ->label('Warna logo mengikuti background')
                                    ->helperText('Logo digambar ulang memakai warna teks di sekitarnya, sehingga tampil putih di kaki halaman yang biru tua. Header selalu memakai warna asli logo. Perlu logo berlatar transparan.')
                                    ->default(true),
                                Forms\Components\Placeholder::make('logo_preview')
                                    ->label('Pratinjau logo')
                                    ->content(fn (Get $get): View => static::logoPreview($get))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('Bagian Pembuka')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\TextInput::make('hero_badge')
                                    ->label('Label kecil di atas judul')
                                    ->maxLength(255)
                                    ->placeholder('Aplikasi Kasir Offline & Online')
                                    ->helperText('Tulisan pendek berbentuk pita di atas judul utama. Kosongkan kalau tidak perlu.'),
                                Forms\Components\TextInput::make('hero_title')
                                    ->label('Judul utama')
                                    ->helperText('Kalimat pertama yang dibaca pengunjung. Sebaiknya menyebut manfaat untuk pemilik toko, bukan nama fiturnya.'),
                                Forms\Components\Textarea::make('hero_subtitle')
                                    ->label('Kalimat penjelas')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('hero_image')
                                    ->label('Gambar aplikasi')
                                    ->image()
                                    ->disk('public')
                                    ->helperText('Tangkapan layar aplikasi di ponsel. Kalau dikosongkan, bagian pembuka memakai panel gradasi teal sebagai gantinya.'),
                                Forms\Components\Textarea::make('hero_stats')
                                    ->label('Angka-angka')
                                    ->rows(3)
                                    ->placeholder("5.000+|Toko sudah memakai\n4,8/5|Penilaian pengguna")
                                    ->helperText('Satu angka per baris, dipisah tanda | menjadi angkanya dan keterangannya.')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('hero_primary_text')
                                    ->label('Tombol utama — tulisan')
                                    ->placeholder('Coba Gratis Sekarang'),
                                Forms\Components\TextInput::make('hero_primary_url')
                                    ->label('Tombol utama — tautan')
                                    ->placeholder('Kosong berarti membuka WhatsApp'),
                                Forms\Components\TextInput::make('hero_secondary_text')
                                    ->label('Tombol kedua — tulisan')
                                    ->placeholder('Lihat Fitur'),
                                Forms\Components\TextInput::make('hero_secondary_url')
                                    ->label('Tombol kedua — tautan')
                                    ->placeholder('#fitur'),
                                Forms\Components\TextInput::make('hero_note')
                                    ->label('Catatan di bawah tombol')
                                    ->maxLength(255)
                                    ->placeholder('Tanpa biaya bulanan, data tersimpan di perangkat Anda.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('Judul Bagian')
                            ->icon('heroicon-o-rectangle-group')
                            ->schema([
                                Forms\Components\TextInput::make('features_heading')
                                    ->label('Fitur — judul')
                                    ->placeholder('Semua yang Dibutuhkan Kasir'),
                                Forms\Components\TextInput::make('features_subheading')
                                    ->label('Fitur — subjudul'),
                                Forms\Components\TextInput::make('packages_heading')
                                    ->label('Paket harga — judul')
                                    ->placeholder('Pilih Paket Sesuai Ukuran Toko'),
                                Forms\Components\TextInput::make('packages_subheading')
                                    ->label('Paket harga — subjudul'),
                                Forms\Components\TextInput::make('gallery_heading')
                                    ->label('Tampilan aplikasi — judul')
                                    ->placeholder('Tampilan Aplikasi'),
                                Forms\Components\TextInput::make('gallery_subheading')
                                    ->label('Tampilan aplikasi — subjudul'),
                                Forms\Components\TextInput::make('steps_heading')
                                    ->label('Cara mulai — judul')
                                    ->placeholder('Mulai Pakai LajuKasir'),
                                Forms\Components\TextInput::make('steps_subheading')
                                    ->label('Cara mulai — subjudul'),
                                Forms\Components\TextInput::make('faq_heading')
                                    ->label('Tanya jawab — judul')
                                    ->placeholder('Pertanyaan yang Sering Diajukan'),
                                Forms\Components\TextInput::make('faq_subheading')
                                    ->label('Tanya jawab — subjudul'),
                                Forms\Components\TextInput::make('testimonials_heading')
                                    ->label('Testimoni — judul')
                                    ->placeholder('Kata Pemilik Toko'),
                                Forms\Components\TextInput::make('testimonials_subheading')
                                    ->label('Testimoni — subjudul'),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('Cara Pasang')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->schema([
                                Forms\Components\TextInput::make('download_heading')
                                    ->label('Judul bagian')
                                    ->placeholder('Pasang Aplikasinya'),
                                Forms\Components\TextInput::make('download_subheading')
                                    ->label('Subjudul bagian'),
                                Forms\Components\TextInput::make('download_android_label')
                                    ->label('Tombol Android — tulisan')
                                    ->placeholder('Unduh untuk Android'),
                                Forms\Components\TextInput::make('download_android_url')
                                    ->label('Tombol Android — tautan')
                                    ->placeholder('https://play.google.com/store/apps/details?id=id.lajukasir'),
                                Forms\Components\TextInput::make('download_windows_label')
                                    ->label('Tombol Windows — tulisan')
                                    ->placeholder('Unduh untuk Windows'),
                                Forms\Components\TextInput::make('download_windows_url')
                                    ->label('Tombol Windows — tautan'),
                                Forms\Components\TextInput::make('download_note')
                                    ->label('Catatan di bawah tombol')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('Ajakan & Kaki Halaman')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Forms\Components\Section::make('Ajakan bertindak penutup')
                                    ->description('Bagian bergradasi teal di bagian bawah halaman, tepat sebelum kaki halaman.')
                                    ->schema([
                                        Forms\Components\TextInput::make('cta_title')
                                            ->label('Judul')
                                            ->placeholder('Siap Membuat Toko Anda Lebih Rapi?'),
                                        Forms\Components\TextInput::make('cta_button_text')
                                            ->label('Tulisan tombol')
                                            ->placeholder('Tanya Lewat WhatsApp'),
                                        Forms\Components\Textarea::make('cta_description')
                                            ->label('Kalimat penjelas')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('cta_button_url')
                                            ->label('Tautan tombol')
                                            ->placeholder('Kosong berarti membuka WhatsApp')
                                            ->helperText('Kalau diisi tautan WhatsApp, klik tombolnya ikut terhitung di Analitik LajuKasir.'),
                                        Forms\Components\TextInput::make('cta_disclaimer')
                                            ->label('Catatan kecil di bawah tombol')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                                Forms\Components\Section::make('Kaki halaman')
                                    ->schema([
                                        Forms\Components\Textarea::make('footer_description')
                                            ->label('Kalimat tentang aplikasi')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('footer_address')
                                            ->label('Alamat')
                                            ->rows(2),
                                        Forms\Components\TextInput::make('footer_hours')
                                            ->label('Jam layanan')
                                            ->placeholder('Senin-Minggu jam 09.00 – 17.00'),
                                        Forms\Components\TextInput::make('footer_copyright')
                                            ->label('Baris hak cipta')
                                            ->maxLength(255)
                                            ->placeholder('© 2026 LajuKasir. Hak Cipta Dilindungi.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('WhatsApp')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Forms\Components\Toggle::make('whatsapp_enabled')
                                    ->label('Tampilkan tombol WhatsApp mengambang')
                                    ->helperText('Kartu obrolannya sama persis dengan yang dipakai situs DigiPro Solution. Tombol "Hubungi Kami" di header tetap memakai nomor di bawah, terlepas dari pilihan ini.')
                                    ->default(true),
                                Forms\Components\TextInput::make('whatsapp_number')
                                    ->label('Nomor WhatsApp')
                                    ->tel()
                                    ->maxLength(32)
                                    ->placeholder(Setting::DEFAULT_WHATSAPP_NUMBER)
                                    ->regex('/^\+?[0-9\s().-]+$/')
                                    ->validationMessages([
                                        'regex' => 'Nomor hanya boleh berisi angka, spasi, tanda +, tanda -, titik, atau tanda kurung.',
                                    ])
                                    ->helperText('Boleh ditulis 0896..., +62 896... atau 62896.... Nomor ini dipakai seluruh tombol WhatsApp di halaman LajuKasir.'),
                                Forms\Components\TextInput::make('whatsapp_greeting')
                                    ->label('Sapaan di balon obrolan')
                                    ->maxLength(255)
                                    ->placeholder(Setting::DEFAULT_WHATSAPP_GREETING)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('whatsapp_message')
                                    ->label('Pesan yang sudah terisi di chat')
                                    ->maxLength(255)
                                    ->placeholder(Setting::DEFAULT_WHATSAPP_MESSAGE)
                                    ->helperText('Pesan ini sudah terketik saat pengunjung membuka WhatsApp, sehingga admin tahu asalnya dari halaman LajuKasir.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Forms\Components\Tabs\Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('Judul halaman (meta title)')
                                    ->maxLength(70)
                                    ->placeholder(fn (): string => Setting::current()->siteName().' - '.Setting::DEFAULT_META_TITLE_SUFFIX)
                                    ->helperText('Idealnya 50-60 karakter. Kosong berarti nama situs ditambah akhiran "'.Setting::DEFAULT_META_TITLE_SUFFIX.'".'),
                                Forms\Components\Textarea::make('meta_description')
                                    ->label('Deskripsi halaman (meta description)')
                                    ->rows(3)
                                    ->maxLength(320)
                                    ->placeholder(Setting::DEFAULT_META_DESCRIPTION)
                                    ->helperText('Idealnya 120-160 karakter. Tampil di bawah judul pada hasil pencarian dan saat tautannya dibagikan.')
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('meta_keywords')
                                    ->label('Kata kunci')
                                    ->maxLength(255)
                                    ->placeholder(Setting::DEFAULT_META_KEYWORDS)
                                    ->helperText('Pisahkan dengan koma, misalnya: aplikasi kasir, software kasir, pos toko.')
                                    ->columnSpanFull(),
                                Forms\Components\FileUpload::make('og_image')
                                    ->label('Gambar pratinjau (og:image)')
                                    ->image()
                                    // Sama seperti logo: pengaturannya menyajikan
                                    // gambar ini dari disk publik.
                                    ->disk('public')
                                    ->helperText('Tampil saat tautan halaman ini dibagikan di WhatsApp, Facebook atau LinkedIn. Ukuran yang disarankan 1200 x 630 piksel.'),
                                Forms\Components\Toggle::make('robots_index')
                                    ->label('Izinkan mesin pencari mengindeks halaman ini')
                                    ->helperText('Matikan selama isinya masih disiapkan: robots.txt akan menutup halaman ini dan halamannya diberi penanda noindex.')
                                    ->default(true),
                            ])
                            ->columns(2),
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
        // Pengaturan LajuKasir hanya satu baris: yang bisa dilakukan hanyalah
        // mengubahnya.
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\EditSetting::route('/'),
        ];
    }

    /**
     * Preview of the logo as it appears on the LajuKasir page header and footer.
     *
     * The view is the same one the DigiPro settings screen uses, because the
     * logo is rendered by the shared x-site-logo component.
     *
     * The LajuKasir header always shows the uploaded file in its own colours,
     * so the top tile of the preview is rendered that way instead of following
     * the adaptive colour toggle.
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
            'headerAdaptive' => false,
            'headerCaption' => 'Header halaman LajuKasir (warna asli logo)',
            'name' => (string) ($get('site_name') ?: Setting::DEFAULT_SITE_NAME),
            'ratio' => Setting::aspectRatioFor($storedPath),
        ]);
    }
}
