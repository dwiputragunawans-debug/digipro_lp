@props([
    'title' => null,
    'description' => null,
    'setting' => null,
    'noindex' => false,
])

{{--
    Seluruh tag SEO satu halaman: judul, deskripsi, kata kunci, aturan indeks,
    alamat kanonis, pratinjau tautan saat halaman dibagikan (Open Graph dan
    Twitter Card) serta data terstruktur untuk Google.

    Isinya diatur di menu Pengaturan Situs (DigiPro Solution) atau Pengaturan
    LajuKasir, dengan nilai bawaan yang masuk akal supaya halaman tetap punya
    judul dan deskripsi sebelum admin mengisinya.

    Halaman lain bisa memakai komponen ini dengan mengirim prop sendiri:

        <x-seo-meta :title="..." :description="..." />

    Halaman LajuKasir mengirim pengaturannya lewat prop setting, dan halaman
    pratinjau lokal mengirim noindex supaya halaman kembar itu tidak terindeks.
--}}
@php
    // Bawaannya pengaturan DigiPro Solution, supaya halaman lama tidak perlu
    // diubah saat komponen ini dipakai bersama.
    $setting ??= \App\Models\SiteSetting::current();

    $pageTitle = $title ?: $setting->metaTitle();
    $pageDescription = $description ?: $setting->metaDescription();
    $canonical = $setting->canonicalUrl();
    $ogImage = $setting->absoluteUrl($setting->ogImageUrl());
    $logo = $setting->absoluteUrl($setting->logoUrl());
    $indexable = ! $noindex && $setting->isIndexable();

    // Data terstruktur: mesin pencari tahu ini situs siapa, alamatnya di mana
    // dan nomor apa yang bisa dihubungi. Logo dan telepon ikut diisi kalau
    // memang ada, karena Google menampilkannya di hasil pencarian.
    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'Organization',
                '@id' => $canonical.'#organization',
                'name' => $setting->siteName(),
                'url' => $canonical,
                'logo' => $logo,
                'telephone' => '+'.$setting->whatsappNumber(),
            ]),
            [
                '@type' => 'WebSite',
                '@id' => $canonical.'#website',
                'name' => $setting->siteName(),
                'url' => $canonical,
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
                'publisher' => ['@id' => $canonical.'#organization'],
            ],
        ],
    ];
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="{{ implode(', ', $setting->metaKeywords()) }}">
<meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Pratinjau tautan di WhatsApp, Facebook, LinkedIn dan sejenisnya. --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $setting->siteName() }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
