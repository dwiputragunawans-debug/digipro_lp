{{--
    Halaman Analitik LajuKasir.

    Isinya seluruhnya widget (lihat App\Filament\Pages\LajukasirAnalytics), jadi
    yang perlu dijelaskan di sini hanya dari mana angkanya datang dan apa yang
    tidak ikut dihitung.
--}}
<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Angka di halaman ini hanya menghitung kunjungan ke
        <strong>{{ config('lajukasir.host') }}</strong>, yaitu halaman LajuKasir.
        Kunjungan ke halaman DigiPro Solution tetap dihitung di
        <a href="{{ \App\Filament\Resources\SiteSettingResource::getUrl('index') }}" class="text-primary-600 dark:text-primary-400 hover:underline">dasbor</a>
        dan menu pengaturan situs, sehingga kedua halaman tidak saling mengganggu angka.
        Pengunjung dihitung sebagai orang berbeda per hari, tanpa menyimpan alamat IP.
    </p>
</x-filament-panels::page>
