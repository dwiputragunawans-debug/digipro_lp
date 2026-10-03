@props([
    'links' => [],
    'heading' => null,
])

{{--
    Deretan ikon media sosial di kaki halaman.

    Isinya berasal dari alamat yang diisi admin di menu Pengaturan Situs
    (DigiPro Solution) atau Pengaturan LajuKasir. Media sosial yang kolomnya
    dikosongkan tidak ikut dirender, jadi tidak ada ikon yang menuju halaman
    kosong. Urutan dan gambar ikonnya diatur di App\Support\Social\Platforms.

    Halaman lain bisa memakai komponen ini dengan mengirim daftarnya:

        <x-social-links :links="$siteSetting->socialLinks()" heading="Media Sosial" />
--}}
@php
    // Daftarnya disaring lagi di sini supaya komponennya tetap aman dipakai
    // walau pengirimnya lupa menyaring alamat yang kosong.
    $links = collect($links)->filter(fn (array $link): bool => filled($link['url'] ?? null))->values();
@endphp

@if ($links->isNotEmpty())
    <div {{ $attributes->merge(['class' => '']) }}>
        @if (filled($heading))
            <h4 class="mb-6 text-sm font-bold uppercase tracking-wider text-white">{{ $heading }}</h4>
        @endif

        {{-- Ikonnya memakai warna teks di sekitarnya, sehingga deretan ini tetap
             terbaca di kaki halaman yang gelap maupun terang. Tautannya dibuka
             di tab baru supaya pengunjung tidak meninggalkan halaman ini. --}}
        <ul class="flex flex-wrap items-center gap-3">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ $link['url'] }}"
                        @if ($link['is_whatsapp'] ?? false) data-whatsapp-click @endif
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="{{ $link['label'] }}"
                        title="{{ $link['label'] }}"
                        class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $link['icon'] }}" /></svg>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
