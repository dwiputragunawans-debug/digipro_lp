<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Judul, deskripsi, kanonis, pratinjau tautan dan data terstruktur halaman LajuKasir. --}}
    <x-seo-meta :setting="$setting" :noindex="$noindex" />
    {{-- Ikon di tab browser dan bookmark, diatur di menu Pengaturan LajuKasir. --}}
    <x-favicon-links :setting="$setting" />
    {{-- Dipakai resources/js/app.js untuk melaporkan klik tombol WhatsApp. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="whatsapp-click-endpoint" content="{{ route('analytics.whatsapp-click') }}">
    @vite(['resources/css/lajukasir.css', 'resources/js/app.js'])
    {{-- Huruf yang sama dengan landing page dan panel DigiPro Solution, supaya
         kedua situs terasa satu keluarga. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-brand-600 selection:text-white">
@php
    // Judul setiap bagian punya nilai bawaan supaya halamannya tetap enak dibaca
    // sebelum admin mengisi apa pun, sama seperti landing page DigiPro Solution.
    // Bagian yang daftar isinya masih kosong tidak ditampilkan sama sekali,
    // sehingga tidak ada judul yang menggantung tanpa isi.
    $heading = fn (string $column, string $fallback): string => filled($setting->{$column}) ? (string) $setting->{$column} : $fallback;
    $subheading = fn (string $column): ?string => filled($setting->{$column}) ? (string) $setting->{$column} : null;

    $showFeatures = $features->isNotEmpty();
    $showPackages = $packages->isNotEmpty();
    $showGallery = $screenshots->isNotEmpty();
    $showSteps = $steps->isNotEmpty();
    $showFaq = $faqs->isNotEmpty();
    $showTestimonials = $testimonials->isNotEmpty();
    $showDownload = filled($setting->download_android_url) || filled($setting->download_windows_url);

    $heroPrimaryUrl = $setting->heroPrimaryUrl();
    $ctaUrl = $setting->ctaButtonUrl();

    // Menu header, dipakai dua kali: mendatar di layar lebar dan bertumpuk di
    // dalam menu ponsel serta tablet. Bagian yang isinya masih kosong tidak
    // ikut masuk daftar, sama seperti menunya selama ini.
    $navLinks = array_values(array_filter([
        $showFeatures ? ['href' => '#fitur', 'label' => 'Fitur'] : null,
        $showPackages ? ['href' => '#paket', 'label' => 'Paket Harga'] : null,
        $showGallery ? ['href' => '#tampilan', 'label' => 'Tampilan'] : null,
        $showSteps ? ['href' => '#cara', 'label' => 'Cara Mulai'] : null,
        $showFaq ? ['href' => '#faq', 'label' => 'Tanya Jawab'] : null,
    ]));
@endphp

{{-- Navbar --}}
<nav data-nav class="sticky top-0 z-50 border-b border-brand-100 bg-white/85 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between gap-6">
            <a href="#" class="flex min-w-0 items-center gap-3">
                @if ($setting->hasLogo())
                    {{-- Di header, logo dipakai apa adanya dengan warna aslinya —
                         hijau teal dan biru tua — karena latarnya putih, jadi
                         tidak perlu diwarnai ulang seperti di kaki halaman.
                         Pilihan "warna logo mengikuti background" di panel admin
                         hanya berlaku untuk bagian yang berlatar gelap. --}}
                    <x-site-logo
                        :url="$setting->logoUrl()"
                        :adaptive="false"
                        :ratio="$setting->logoAspectRatio()"
                        :name="$setting->siteName()"
                        height="2.75rem"
                    />
                @else
                    {{-- Tanpa logo yang diunggah, namanya ditulis dengan warna logonya:
                         "Laju" hijau teal dan "Kasir" biru tua. --}}
                    <span class="text-3xl font-black tracking-tighter text-brand-600">Laju<span class="text-ink-900">Kasir</span></span>
                @endif
            </a>

            <div class="flex items-center gap-3">
                {{-- Menu mendatar hanya di layar lebar; di ponsel dan tablet
                     digantikan tombol menu di sebelahnya. Bagian yang isinya
                     masih kosong tidak ikut masuk daftar menu. --}}
                <div class="hidden items-center gap-7 lg:flex">
                    @foreach ($navLinks as $link)
                        <a href="{{ $link['href'] }}" class="text-sm font-medium text-slate-600 transition-colors hover:text-brand-700">{{ $link['label'] }}</a>
                    @endforeach
                    {{-- Tombol WhatsApp dibuka di tab baru supaya halaman ini
                         tetap terbuka saat pengunjung mulai mengobrol. --}}
                    <a href="{{ $setting->whatsappUrl() }}" data-whatsapp-click target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-brand-600/20 transition-all duration-300 hover:-translate-y-0.5 hover:from-brand-700 hover:to-brand-600 hover:shadow-lg">
                        Hubungi Kami
                    </a>
                </div>

                <button
                    type="button"
                    data-nav-toggle
                    aria-expanded="false"
                    aria-controls="primary-navigation"
                    aria-label="Buka menu navigasi"
                    class="grid h-11 w-11 place-items-center rounded-xl border border-brand-200 text-ink-800 transition-colors hover:bg-brand-50 lg:hidden"
                >
                    {{-- Dua ikon sekaligus; JavaScript yang menggilirnya, jadi
                         tidak ada permintaan tambahan ke server. --}}
                    <svg data-nav-icon-open class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg data-nav-icon-close class="hidden h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        {{-- Daftar menu yang dibuka tombol di atas. Sama isinya dengan menu
             mendatar, hanya disusun bertumpuk untuk jempol. --}}
        <div id="primary-navigation" data-nav-panel class="hidden border-t border-brand-100 py-4 lg:hidden">
            <div class="flex flex-col">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}" class="rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-brand-50 hover:text-brand-700">{{ $link['label'] }}</a>
                @endforeach
                <a href="{{ $setting->whatsappUrl() }}" data-whatsapp-click target="_blank" rel="noopener" class="mt-3 inline-flex items-center justify-center rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-brand-600/20 transition-colors hover:from-brand-700 hover:to-brand-600">Hubungi Kami</a>
            </div>
        </div>
    </div>
</nav>

{{-- Bagian pembuka: gradasinya menyeberang dari biru tua ke hijau teal, sama
     seperti dua warna pada logo. --}}
<section class="relative overflow-hidden bg-gradient-to-br from-ink-950 via-ink-900 to-brand-700">
    <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 18% 18%, rgba(255,255,255,0.4) 0, transparent 45%), radial-gradient(circle at 82% 65%, rgba(255,255,255,0.3) 0, transparent 40%);"></div>

    <div class="relative mx-auto grid max-w-7xl gap-16 px-4 py-24 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-32">
        <div>
            @if (filled($setting->hero_badge))
                <span class="mb-6 inline-flex items-center rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-50 ring-1 ring-inset ring-white/20">{{ $setting->hero_badge }}</span>
            @endif

            <h1 class="text-4xl font-extrabold leading-tight text-white md:text-5xl lg:text-6xl">
                {{ $setting->hero_title ?: 'Kasir Toko yang Tetap Jalan Walau Internet Mati.' }}
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-brand-100 md:text-xl">
                {{ $setting->hero_subtitle ?: 'LajuKasir mencatat penjualan, stok, piutang dan laporan harian langsung di perangkat toko Anda. Data tersimpan di perangkat sendiri, jadi kasir tetap bisa dipakai walau koneksi sedang putus.' }}
            </p>

            <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                <a
                    href="{{ $heroPrimaryUrl }}"
                    @if ($setting->isWhatsAppLink($heroPrimaryUrl)) data-whatsapp-click target="_blank" rel="noopener" @endif
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-8 py-3.5 text-base font-semibold text-brand-800 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-brand-50 hover:shadow-xl"
                >
                    <x-lajukasir.whatsapp-icon class="h-5 w-5" />
                    {{ $setting->hero_primary_text ?: 'Coba Gratis Sekarang' }}
                </a>
                <a href="{{ $setting->hero_secondary_url ?: '#fitur' }}" class="inline-flex items-center justify-center rounded-full border border-white/70 px-8 py-3.5 text-base font-semibold text-white transition-all duration-300 hover:bg-white/10 hover:shadow-xl">
                    {{ $setting->hero_secondary_text ?: 'Lihat Fiturnya' }}
                </a>
            </div>

            @if (filled($setting->hero_note))
                <p class="mt-6 text-sm font-medium text-brand-200">{{ $setting->hero_note }}</p>
            @endif

            @if (! empty($setting->hero_stats_list))
                <dl class="mt-12 grid grid-cols-2 gap-6 sm:grid-cols-3">
                    @foreach ($setting->hero_stats_list as $stat)
                        <div>
                            <dt class="text-2xl font-extrabold text-white sm:text-3xl">{{ $stat['value'] }}</dt>
                            @if (filled($stat['label']))
                                <dd class="mt-1 text-xs font-medium uppercase tracking-wide text-brand-200">{{ $stat['label'] }}</dd>
                            @endif
                        </div>
                    @endforeach
                </dl>
            @endif
        </div>

        {{-- Gambar aplikasi dari menu Pengaturan LajuKasir. Kalau belum diunggah,
             yang tampil adalah bingkai bergradasi supaya tata letaknya tetap utuh. --}}
        <div class="relative">
            @if ($setting->hasHeroImage())
                <img
                    src="{{ $setting->heroImageUrl() }}"
                    alt="Tampilan aplikasi {{ $setting->siteName() }}"
                    class="mx-auto w-full max-w-sm rounded-[2rem] shadow-2xl ring-1 ring-white/20"
                >
            @else
                <div class="mx-auto flex h-80 w-full max-w-sm items-center justify-center rounded-[2rem] bg-white/10 p-8 text-center text-sm font-medium leading-relaxed text-brand-100 ring-1 ring-inset ring-white/20">
                    Unggah gambar aplikasi di menu Pengaturan LajuKasir untuk mengganti panel ini.
                </div>
            @endif
        </div>
    </div>
</section>

{{-- Fitur unggulan --}}
@if ($showFeatures)
    <section id="fitur" class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('features_heading', 'Semua yang Dibutuhkan untuk Mengelola Toko')"
                :subheading="$subheading('features_subheading')"
            />

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    {{-- Isi kartu diambil dari menu Fitur Unggulan di panel admin. --}}
                    <x-lajukasir.feature-card
                        :icon="$feature->icon_name"
                        :title="$feature->title"
                        :description="$feature->description"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Paket harga --}}
@if ($showPackages)
    <section id="paket" class="bg-gradient-to-b from-brand-50 to-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('packages_heading', 'Pilih Paket Sesuai Ukuran Toko')"
                :subheading="$subheading('packages_subheading')"
            />

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($packages as $package)
                    @php
                        // Tautan tombol paket memakai WhatsApp kalau admin belum
                        // mengisi tautannya sendiri.
                        $packageUrl = $setting->whatsappUrlWithFilledText($package->cta_url) ?: $setting->whatsappUrl();
                    @endphp

                    {{-- Isi kartu diambil dari menu Paket Harga di panel admin. --}}
                    <x-lajukasir.package-card
                        :name="$package->name"
                        :price="$package->price"
                        :price-unit="$package->price_unit"
                        :description="$package->description"
                        :badge="$package->badge"
                        :benefits="$package->feature_list"
                        :highlighted="$package->is_highlighted"
                        :cta-text="$package->cta_text"
                        :cta-url="$packageUrl"
                        :track="$setting->isWhatsAppLink($packageUrl)"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Tampilan aplikasi --}}
@if ($showGallery)
    <section id="tampilan" class="bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('gallery_heading', 'Tampilan Aplikasi')"
                :subheading="$subheading('gallery_subheading')"
            />

            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($screenshots as $screenshot)
                    {{-- Gambar diambil dari menu Tampilan Aplikasi di panel admin. --}}
                    <x-lajukasir.screenshot-card
                        :image="$screenshot->imageUrl()"
                        :title="$screenshot->title"
                        :caption="$screenshot->caption"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Cara mulai --}}
@if ($showSteps)
    <section id="cara" class="border-y border-brand-100 bg-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('steps_heading', 'Mulai Pakai LajuKasir')"
                :subheading="$subheading('steps_subheading')"
            />

            @php
                // Jumlah kolom mengikuti jumlah langkah supaya semuanya tampil dalam satu
                // baris di layar lebar (contoh: 5 langkah = 5 kolom di lg).
                $stepCount = max(1, $steps->count());

                $stepGrid = match (true) {
                    $stepCount >= 5 => 'sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
                    $stepCount === 4 => 'md:grid-cols-4',
                    $stepCount === 3 => 'md:grid-cols-3',
                    $stepCount === 2 => 'md:grid-cols-2',
                    default => '',
                };

                // Garis penghubung hanya tampil saat semua langkah benar-benar sebaris,
                // dan ujungnya berhenti di titik tengah lingkaran langkah pertama dan
                // terakhir (gap-12 = 3rem, jadi 48px per celah antar kolom).
                $connectorInset = 'calc((100% - '.((($stepCount - 1) * 48)).'px) / '.($stepCount * 2).')';
            @endphp

            <div class="relative grid grid-cols-1 {{ $stepGrid }} gap-12">
                @if ($stepCount > 1)
                    {{-- Garis penghubung --}}
                    <div class="absolute top-12 z-0 hidden h-0.5 bg-gradient-to-r from-brand-200 via-brand-300 to-brand-200 {{ $stepCount >= 5 ? 'lg:block' : 'md:block' }}"
                        style="left: {{ $connectorInset }}; right: {{ $connectorInset }};"></div>
                @endif

                @foreach ($steps as $index => $step)
                    {{-- Isi langkah diambil dari menu Cara Mulai di panel admin. --}}
                    <x-lajukasir.step-item
                        :number="$index + 1"
                        :icon="$step->step_icon"
                        :title="$step->title"
                        :description="$step->description"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Unduh aplikasi --}}
@if ($showDownload)
    <section id="pasang" class="bg-gradient-to-b from-white to-brand-50 py-24">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('download_heading', 'Pasang Aplikasinya')"
                :subheading="$subheading('download_subheading')"
            />

            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                @if (filled($setting->download_android_url))
                    <a href="{{ $setting->download_android_url }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-brand-600/20 transition-all duration-300 hover:-translate-y-1 hover:from-brand-700 hover:to-brand-600 hover:shadow-xl">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                        {{ $setting->download_android_label ?: 'Unduh untuk Android' }}
                    </a>
                @endif

                @if (filled($setting->download_windows_url))
                    <a href="{{ $setting->download_windows_url }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-brand-300 bg-white px-8 py-3.5 text-base font-semibold text-brand-800 transition-all duration-300 hover:-translate-y-1 hover:border-brand-400 hover:shadow-lg">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ $setting->download_windows_label ?: 'Unduh untuk Windows' }}
                    </a>
                @endif
            </div>

            @if (filled($setting->download_note))
                <p class="mt-6 text-sm text-slate-500">{{ $setting->download_note }}</p>
            @endif
        </div>
    </section>
@endif

{{-- Pertanyaan umum --}}
@if ($showFaq)
    <section id="faq" class="bg-white py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('faq_heading', 'Pertanyaan yang Sering Diajukan')"
                :subheading="$subheading('faq_subheading')"
            />

            <div class="space-y-4">
                @foreach ($faqs as $faq)
                    {{-- Isi tanya jawab diambil dari menu Pertanyaan Umum di panel admin. --}}
                    <x-lajukasir.faq-item :question="$faq->question" :answer="$faq->answer" />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Testimoni --}}
@if ($showTestimonials)
    <section id="testimoni" class="bg-gradient-to-b from-brand-50 to-white py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-lajukasir.section-heading
                :heading="$heading('testimonials_heading', 'Kata Pemilik Toko')"
                :subheading="$subheading('testimonials_subheading')"
            />

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    {{-- Isi kartu diambil dari menu Testimoni di panel admin. --}}
                    <x-lajukasir.testimonial-card
                        :name="$testimonial->name"
                        :store="$testimonial->store"
                        :quote="$testimonial->quote"
                        :photo="$testimonial->photoUrl()"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Ajakan bertindak penutup --}}
<section class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-ink-900 via-brand-800 to-brand-600 px-8 py-16 text-center shadow-2xl lg:px-20">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 15% 20%, rgba(255,255,255,0.5) 0, transparent 45%), radial-gradient(circle at 85% 75%, rgba(255,255,255,0.4) 0, transparent 40%);"></div>

            <div class="relative">
                <h2 class="text-3xl font-bold text-white md:text-4xl">
                    {{ $setting->cta_title ?: 'Siap Membuat Catatan Toko Anda Lebih Rapi?' }}
                </h2>

                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-brand-50">
                    {{ $setting->cta_description ?: 'Tanya dulu saja, kami bantu pilihkan paket yang paling pas untuk ukuran toko Anda.' }}
                </p>

                <div class="mt-10 flex flex-col items-center">
                    <a
                        href="{{ $ctaUrl }}"
                        @if ($setting->isWhatsAppLink($ctaUrl)) data-whatsapp-click target="_blank" rel="noopener" @endif
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-10 py-4 text-lg font-bold text-brand-800 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:bg-brand-50"
                    >
                        <x-lajukasir.whatsapp-icon class="h-5 w-5" />
                        {{ $setting->cta_button_text ?: 'Tanya Lewat WhatsApp' }}
                    </a>

                    @if (filled($setting->cta_disclaimer))
                        <p class="mt-4 text-sm font-medium text-brand-100">{{ $setting->cta_disclaimer }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Kaki halaman --}}
<footer class="bg-ink-950 py-16 text-ink-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-4">
            <div class="md:col-span-2">
                @if ($setting->hasLogo())
                    <x-site-logo
                        :url="$setting->logoUrl()"
                        :adaptive="$setting->logo_adaptive"
                        :ratio="$setting->logoAspectRatio()"
                        :name="$setting->siteName()"
                        height="2.75rem"
                        class="mb-6 text-white"
                    />
                @else
                    <div class="mb-6 text-3xl font-black tracking-tighter text-white">Laju<span class="text-brand-400">Kasir</span></div>
                @endif

                {{-- Slogan hanya tampil di kaki halaman; di header, ruangnya
                     dipakai logonya saja. --}}
                <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-brand-400">{{ $setting->tagline() }}</p>

                <p class="max-w-sm text-sm leading-relaxed text-ink-300">
                    {{ $setting->footer_description ?: 'LajuKasir membantu toko, kios dan warung mencatat penjualan dengan rapi tanpa harus bergantung pada koneksi internet.' }}
                </p>
            </div>

            @if ($showFeatures || $showPackages || $showGallery || $showSteps || $showFaq)
                <div>
                    <h4 class="mb-6 text-sm font-bold uppercase tracking-wider text-white">Halaman</h4>
                    <ul class="space-y-4 text-sm">
                        @if ($showFeatures)
                            <li><a href="#fitur" class="transition-colors hover:text-white">Fitur</a></li>
                        @endif
                        @if ($showPackages)
                            <li><a href="#paket" class="transition-colors hover:text-white">Paket Harga</a></li>
                        @endif
                        @if ($showGallery)
                            <li><a href="#tampilan" class="transition-colors hover:text-white">Tampilan Aplikasi</a></li>
                        @endif
                        @if ($showSteps)
                            <li><a href="#cara" class="transition-colors hover:text-white">Cara Mulai</a></li>
                        @endif
                        @if ($showFaq)
                            <li><a href="#faq" class="transition-colors hover:text-white">Tanya Jawab</a></li>
                        @endif
                    </ul>
                </div>
            @endif

            <div>
                <h4 class="mb-6 text-sm font-bold uppercase tracking-wider text-white">Kontak</h4>
                <ul class="space-y-4 text-sm">
                    @if (filled($setting->footer_address))
                        <li>{{ $setting->footer_address }}</li>
                    @endif

                    @if (filled($setting->footer_hours))
                        <li>{{ $setting->footer_hours }}</li>
                    @endif

                    <li>
                        <a href="{{ $setting->whatsappUrl() }}" data-whatsapp-click target="_blank" rel="noopener" class="font-semibold text-brand-300 transition-colors hover:text-white">WhatsApp: +{{ $setting->whatsappNumber() }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-16 border-t border-ink-800 pt-8 text-center text-sm text-ink-400">
            <p>{{ $setting->footer_copyright ?: '© '.now()->year.' LajuKasir. Hak Cipta Dilindungi.' }}</p>
        </div>
    </div>
</footer>

{{-- Tombol WhatsApp mengambang, sama persis dengan yang dipakai situs DigiPro
     Solution, supaya pengunjung bisa menghubungi admin hanya dengan sekali klik. --}}
@if ($setting->hasWhatsApp())
    <x-whatsapp-widget
        :url="$setting->whatsappUrl()"
        :greeting="$setting->whatsappGreeting()"
    />
@endif

{{-- Tombol kembali ke atas, muncul setelah pengunjung menggulir halaman. --}}
<x-back-to-top color-classes="bg-gradient-to-br from-brand-700 to-brand-500 hover:from-brand-800 hover:to-brand-600" />
</body>
</html>
