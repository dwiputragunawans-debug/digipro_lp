<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Judul, deskripsi, kanonis, pratinjau tautan dan data terstruktur. --}}
    <x-seo-meta />
    {{-- Ikon di tab browser dan bookmark, diatur di menu Pengaturan Situs. --}}
    <x-favicon-links />
    {{-- Dipakai resources/js/app.js untuk melaporkan klik tombol WhatsApp. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="whatsapp-click-endpoint" content="{{ route('analytics.whatsapp-click') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-800 selection:bg-blue-600 selection:text-white">
    @php
        // Menu header, dipakai dua kali: mendatar di layar lebar dan bertumpuk
        // di dalam menu ponsel serta tablet.
        $navLinks = [
            ['href' => '#mengapa', 'label' => 'Mengapa Kami'],
            ['href' => '#solusi', 'label' => 'Solusi'],
            ['href' => '#karya', 'label' => 'Karya'],
            ['href' => '#proses', 'label' => 'Proses'],
        ];
    @endphp

    <!-- Navbar -->
    <nav data-nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center">
                    <a href="#" class="flex items-center gap-2">
                        @if ($siteSetting->hasLogo())
                            <x-site-logo height="2.75rem" class="text-slate-900" />
                        @else
                            <div class="text-3xl font-black text-slate-900 tracking-tighter">digi<span class="text-blue-600">pro</span></div>
                        @endif
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Menu mendatar hanya di layar lebar; di ponsel dan tablet
                         digantikan tombol menu di sebelahnya. --}}
                    <div class="hidden lg:flex items-center space-x-8">
                        @foreach ($navLinks as $link)
                            <a href="{{ $link['href'] }}" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">{{ $link['label'] }}</a>
                        @endforeach
                        {{-- Tombol WhatsApp dibuka di tab baru supaya halaman ini
                             tetap terbuka saat pengunjung mulai mengobrol. --}}
                        <a href="{{ $siteSetting->whatsappUrl() }}" data-whatsapp-click target="_blank" rel="noopener" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-full text-white bg-blue-600 hover:bg-blue-700 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">Hubungi Kami</a>
                    </div>

                    <button
                        type="button"
                        data-nav-toggle
                        aria-expanded="false"
                        aria-controls="primary-navigation"
                        aria-label="Buka menu navigasi"
                        class="lg:hidden grid h-11 w-11 place-items-center rounded-xl border border-slate-200 text-slate-700 transition-colors hover:bg-slate-100"
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
            <div id="primary-navigation" data-nav-panel class="hidden border-t border-slate-100 py-4 lg:hidden">
                <div class="flex flex-col">
                    @foreach ($navLinks as $link)
                        <a href="{{ $link['href'] }}" class="rounded-xl px-4 py-3 text-base font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-blue-600">{{ $link['label'] }}</a>
                    @endforeach
                    <a href="{{ $siteSetting->whatsappUrl() }}" data-whatsapp-click target="_blank" rel="noopener" class="mt-3 inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700">Hubungi Kami</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-slate-900 overflow-hidden py-24 lg:py-32" @if(!empty($hero->background_image)) style="background-image: url('{{ asset('storage/' . $hero->background_image) }}'); background-size: cover; background-position: center;" @endif>
        <div class="absolute inset-0 bg-slate-900/70"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="lg:w-2/3">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                    {{ $hero->title ?? 'Jadikan Bisnis UMKM Anda Lebih Kredibel dan Dipercaya Pelanggan.' }}
                </h1>
                <p class="text-lg md:text-xl text-slate-300 mb-10 max-w-2xl leading-relaxed">
                    {{ $hero->subtitle ?? 'Beralih ke digital dengan website yang profesional dan mudah dikelola, dirancang khusus untuk meningkatkan omzet dan memenangkan persaingan.' }}
                </p>
                @php
                    // Tautan WhatsApp dari admin dipakai apa adanya; hanya kalau
                    // kolom pesannya masih kosong, pesan bawaan situs yang diisi.
                    $heroPrimaryUrl = $siteSetting->whatsappUrlWithFilledText($hero->cta_primary_url ?? null) ?? '#';
                @endphp
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ $heroPrimaryUrl }}" @if ($siteSetting->isWhatsAppLink($heroPrimaryUrl)) data-whatsapp-click target="_blank" rel="noopener" @endif class="inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-semibold rounded-full text-blue-700 bg-white hover:bg-slate-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.898-4.45 9.898-9.898 0-5.448-4.45-9.897-9.898-9.897-5.448 0-9.898 4.45-9.898 9.897 0 2.115.597 3.716 1.591 5.392l-1.121 4.1 4.236-1.186zm10.514-4.249c-.273-.139-1.613-.796-1.863-.888-.25-.093-.432-.139-.614.139-.182.278-.705.888-.864 1.073-.159.185-.318.209-.591.07-2.613-1.332-4.041-2.909-5.595-5.503-.159-.278.149-.247.669-.748.24-.24.364-.37.495-.61.131-.24.066-.453-.027-.638-.093-.185-.614-1.482-.841-2.029-.22-.533-.443-.46-.614-.467-.168-.007-.363-.009-.556-.009-.193 0-.505.071-.773.349-.268.278-1.023 1-1.023 2.436s1.045 2.825 1.19 3.01c.145.185 2.053 3.136 4.97 4.398 2.096.904 2.868 1.055 3.906.883.953-.158 2.836-1.157 3.238-2.276.402-1.119.402-2.078.282-2.276-.12-.198-.433-.306-.706-.445z"/></svg>
                        {{ $hero->cta_primary_text ?? 'WhatsApp' }}
                    </a>
                    <a href="{{ $hero->cta_secondary_url ?? '#' }}" class="inline-flex items-center justify-center px-8 py-3.5 border border-white text-base font-semibold rounded-full text-white hover:bg-white/10 hover:shadow-xl transition-all duration-300">
                        {{ $hero->cta_secondary_text ?? 'Mulai Sekarang' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Mengapa Memilih Kami -->
    <section id="mengapa" class="py-24 bg-blue-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Mengapa Memilih Kami?</h2>
                <p class="mt-4 text-lg text-slate-600">Alasan utama ribuan bisnis mempercayakan go-digital-nya pada kami.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($features ?? [] as $feature)
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mb-6">
                            {{-- Ikon berasal dari kolom "Ikon" di panel admin, dengan ikon petir sebagai cadangan. --}}
                            <x-dynamic-component :component="$feature->icon_name" class="w-6 h-6" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $feature->title }}</h3>
                        <p class="text-slate-600 leading-relaxed">{{ $feature->description }}</p>
                    </div>
                @empty
                    <!-- Fallback -->
                    @foreach(['Desain Mobile Friendly', 'Optimasi SEO Dasar', 'Harga Terjangkau', 'Cepat & Tepat Waktu', 'Aman & Bebas Khawatir', 'Support & Garansi'] as $title)
                    <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 mb-6">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $title }}</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">Memberikan yang terbaik untuk kelangsungan bisnis Anda dengan layanan profesional.</p>
                    </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <!-- Solusi Digital -->
    <section id="solusi" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Solusi Digital untuk UMKM</h2>
                <p class="mt-4 text-lg text-slate-600">Pilih paket layanan yang paling sesuai dengan kebutuhan usaha Anda saat ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @forelse($services ?? [] as $service)
                    @php
                        // Tombol kartu menuju WhatsApp dengan pesan yang menyebut
                        // paketnya, supaya admin tidak perlu menulis satu tautan
                        // wa.me untuk setiap paket. Lihat Service::ctaUrlFor().
                        $serviceUrl = $service->ctaUrlFor($siteSetting);
                    @endphp
                    {{-- Isi kartu diambil dari menu Layanan di panel admin. --}}
                    <x-service-card
                        :icon="$service->icon_name"
                        :title="$service->title"
                        :description="$service->description"
                        :benefits="$service->benefit_list"
                        :badge="$service->badge"
                        :highlighted="$service->is_highlighted"
                        :cta-text="$service->cta_text"
                        :cta-url="$serviceUrl"
                        :cta-whatsapp="$siteSetting->isWhatsAppLink($serviceUrl)"
                    />
                @empty
                    {{-- Kartu contoh kalau menu Layanan masih kosong. --}}
                    <x-service-card
                        icon="heroicon-o-briefcase"
                        title="Website Profil Bisnis"
                        description="Tampil profesional dengan website profil bisnis untuk meningkatkan kredibilitas."
                        :benefits="['Desain Modern & Profesional', 'Domain & Hosting Gratis 1 Tahun', 'Integrasi WhatsApp & Media Sosial']"
                    />
                    <x-service-card
                        icon="heroicon-o-shopping-cart"
                        badge="POPULER"
                        title="Katalog & Toko Online"
                        description="Tingkatkan penjualan dengan toko online yang mudah dikelola, lengkap dengan pembayaran."
                        :benefits="['Sistem Keranjang Belanja', 'Manajemen Produk Mudah', 'Integrasi Metode Pembayaran']"
                    />
                    <x-service-card
                        icon="heroicon-o-code-bracket"
                        highlighted
                        title="Aplikasi Web Custom"
                        description="Sistem khusus yang disesuaikan dengan kebutuhan unik operasional bisnis Anda."
                        :benefits="['Sistem Kasir (POS) Berbasis Web', 'Manajemen Stok/Inventaris', 'Dashboard Laporan & Analitik']"
                    />
                @endforelse
            </div>
        </div>
    </section>

    <!-- Karya Kami -->
    <section id="karya" class="py-24 bg-blue-50/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Karya Kami</h2>
                <p class="mt-4 text-lg text-slate-600">Beberapa project terbaru yang telah kami selesaikan dengan sukses.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                @forelse($portfolios ?? [] as $portfolio)
                    {{-- Isi kartu diambil dari menu Portofolio di panel admin. --}}
                    <x-portfolio-card
                        :image="$portfolio->imageUrl()"
                        :title="$portfolio->title"
                        :link="$portfolio->link"
                    />
                @empty
                    {{-- Kartu contoh kalau menu Portofolio masih kosong. --}}
                    <x-portfolio-card />
                    <x-portfolio-card />
                    <x-portfolio-card />
                    <x-portfolio-card />
                @endforelse
            </div>
        </div>
    </section>

    <!-- Proses -->
    <section id="proses" class="py-24 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20">
                <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Proses Mudah Memiliki Website</h2>
                <p class="mt-4 text-lg text-slate-600">Kami membuat segalanya jadi simpel untuk Anda.</p>
            </div>

            @php
                // Jumlah kolom mengikuti jumlah langkah supaya semuanya tampil dalam satu
                // baris di layar lebar (contoh: 5 langkah = 5 kolom di lg). Kalau tabel
                // Proses masih kosong, yang dihitung adalah empat langkah contoh di bawah.
                $stepCount = max(1, ($processes ?? collect())->count() ?: 4);

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

            <div class="grid grid-cols-1 {{ $stepGrid }} gap-12 relative">
                <!-- Line connector -->
                <div class="hidden {{ $stepCount >= 5 ? 'lg:block' : 'md:block' }} absolute top-12 h-0.5 bg-blue-100 z-0"
                    style="left: {{ $connectorInset }}; right: {{ $connectorInset }};"></div>

                @forelse($processes ?? [] as $index => $process)
                    {{-- Isi langkah diambil dari menu Proses di panel admin. --}}
                    <x-process-step
                        :number="$index + 1"
                        :icon="$process->step_icon"
                        :title="$process->title"
                        :description="$process->description"
                    />
                @empty
                    {{-- Langkah contoh kalau menu Proses masih kosong. --}}
                    <x-process-step :number="1" title="Konsultasi" description="Langkah 1 menuju kesuksesan digital Anda." />
                    <x-process-step :number="2" title="Pembuatan" description="Langkah 2 menuju kesuksesan digital Anda." />
                    <x-process-step :number="3" title="Pengujian" description="Langkah 3 menuju kesuksesan digital Anda." />
                    <x-process-step :number="4" title="Online" description="Langkah 4 menuju kesuksesan digital Anda." />
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section with Image -->
    <section class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="bg-slate-900 rounded-[2.5rem] overflow-hidden flex flex-col lg:flex-row shadow-2xl">
                <div class="lg:w-1/2 relative min-h-[300px]">
                    <!-- Placeholder background image -->
                    <div class="absolute inset-0 bg-slate-800 bg-[url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80')] bg-cover bg-center mix-blend-overlay"></div>
                </div>
                <div class="lg:w-1/2 p-12 lg:p-20 flex flex-col justify-center">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">Kenapa Mempercayakan Digitalisasi Bisnis Anda pada Kami?</h2>
                    <p class="text-slate-300 mb-8 text-lg leading-relaxed">
                        Kami paham bahwa UMKM butuh solusi yang praktis, cepat, dan terjangkau tanpa mengorbankan kualitas. Dengan pengalaman kami, kami siap menjadi partner bertumbuh bisnis Anda.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Blue CTA Banner -->
    <section class="bg-blue-600 py-20">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">{{ $cta->title ?? 'Siap Membawa Bisnis UMKM Anda ke Level Selanjutnya?' }}</h2>
            <p class="text-blue-100 mb-10 text-lg">{{ $cta->description ?? 'Konsultasikan kebutuhan website Anda secara GRATIS.' }}</p>
            <div class="flex flex-col items-center">
                @php
                    // Sama seperti tombol di bagian pembuka: tautan WhatsApp dari
                    // admin dilengkapi pesannya kalau masih kosong.
                    $ctaButtonUrl = $siteSetting->whatsappUrlWithFilledText($cta->button_url ?? null) ?? '#';
                @endphp
                <a href="{{ $ctaButtonUrl }}" @if ($siteSetting->isWhatsAppLink($ctaButtonUrl)) data-whatsapp-click target="_blank" rel="noopener" @endif class="inline-flex items-center justify-center px-10 py-4 border border-transparent text-lg font-bold rounded-full text-blue-700 bg-white hover:bg-slate-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg>
                    {{ $cta->button_text ?? 'Konsultasi Gratis Sekarang' }}
                </a>
                <p class="mt-4 text-white text-sm font-medium">{{ $cta->disclaimer ?? '*Konsultasi awal gratis dan tidak mengikat.' }}</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-300 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-1 md:col-span-2">
                    @if ($siteSetting->hasLogo())
                        <x-site-logo height="2.75rem" class="mb-6 text-white" />
                    @else
                        <div class="text-3xl font-black text-white tracking-tighter mb-6">digi<span class="text-blue-500">pro</span></div>
                    @endif
                    <p class="text-slate-400 max-w-sm mb-6">Mitra terpercaya UMKM dalam melakukan transformasi digital dengan website profesional.</p>

                    {{-- Ikon media sosial yang alamatnya diisi di menu Pengaturan
                         Situs; yang dikosongkan tidak ditampilkan. --}}
                    <x-social-links :links="$siteSetting->socialLinks()" heading="Media Sosial" />
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Layanan</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="hover:text-white transition-colors">Website Profil Bisnis</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Toko Online</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Aplikasi Web Custom</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Kontak</h4>
                    <ul class="space-y-4">
                        <li>Jl. Kedung Buni II No.98A, RT.9/RW.1, Tj. Bar., Kec. Jagakarsa, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12530</li>

                        <li>Senin-Minggu jam 09.00 – 17.00</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-16 pt-8 text-center text-sm text-slate-500 flex flex-col md:flex-row justify-between items-center">
                <p>&copy; 2026 Digipro Solution. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>

    {{-- Tombol WhatsApp mengambang supaya pengunjung bisa menghubungi admin hanya dengan sekali klik. --}}
    @if ($siteSetting->hasWhatsApp())
        <x-whatsapp-widget
            :url="$siteSetting->whatsappUrl()"
            :greeting="$siteSetting->whatsappGreeting()"
        />
    @endif

    {{-- Tombol kembali ke atas, muncul setelah pengunjung menggulir halaman. --}}
    <x-back-to-top />
</body>
</html>
