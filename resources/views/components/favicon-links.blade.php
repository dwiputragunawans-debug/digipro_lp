@props([
    'setting' => null,
])

{{--
    Ikon situs: favicon yang tampil di tab browser, riwayat dan bookmark, plus
    apple-touch-icon untuk pengunjung yang menyimpan halamannya ke layar utama
    iPhone atau iPad.

    Berkasnya diunggah di menu Pengaturan Situs (DigiPro Solution) atau
    Pengaturan LajuKasir. Kalau belum ada, logonya yang dipakai, sehingga ikon
    di tab browser sudah terisi begitu logo diunggah.

    Halaman lain bisa memakai komponen ini dengan mengirim pengaturannya:

        <x-favicon-links :setting="$setting" />
--}}
@php
    $setting ??= \App\Models\SiteSetting::current();

    $iconUrl = $setting->faviconUrl();
    $iconType = $setting->faviconType();
    $appleIconUrl = $setting->appleTouchIconUrl();
@endphp

@if ($iconUrl)
    <link rel="icon" href="{{ $iconUrl }}" @if ($iconType) type="{{ $iconType }}" @endif>
    @if ($appleIconUrl)
        <link rel="apple-touch-icon" href="{{ $appleIconUrl }}">
    @endif
@endif
