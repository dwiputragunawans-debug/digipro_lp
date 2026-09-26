<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subdomain halaman LajuKasir
    |--------------------------------------------------------------------------
    |
    | Landing page LajuKasir dilayani dari subdomain tersendiri, sedangkan
    | panel adminnya tetap satu dengan DigiPro Solution di domain utama.
    | Nilainya dibaca oleh Route::domain() di routes/web.php.
    |
    | Selama subdomainnya belum punya DNS, di komputer pengembang halamannya
    | bisa dibuka lewat salah satu alamat berikut:
    |
    |   http://lajukasir.digipro-solution.com:8000   setelah menambahkan
    |       127.0.0.1 lajukasir.digipro-solution.com   ke /etc/hosts
    |   http://lajukasir.localhost:8000              alias di bawah, tanpa
    |       mengubah berkas apa pun
    |   http://localhost:8000/lajukasir-preview      selama pratinjaunya hidup
    |
    */

    'host' => env('LAJUKASIR_HOST', 'lajukasir.digipro-solution.com'),

    /*
    |--------------------------------------------------------------------------
    | Alias pengembangan
    |--------------------------------------------------------------------------
    |
    | Nama yang berakhiran .localhost selalu diarahkan kembali ke komputer yang
    | membukanya, jadi alamat ini bisa dipakai tanpa menyunting /etc/hosts dan
    | tidak mungkin menunjuk ke server ini dari perangkat orang lain.
    | Kosongkan nilainya untuk mematikan aliasnya.
    |
    */

    'local_host' => env('LAJUKASIR_LOCAL_HOST', 'lajukasir.localhost'),

    /*
    |--------------------------------------------------------------------------
    | Alamat kanonis halaman LajuKasir
    |--------------------------------------------------------------------------
    |
    | Dipakai untuk tag canonical, og:url, sitemap.xml dan robots.txt halaman
    | LajuKasir. Sama seperti SITE_URL milik DigiPro Solution, nilainya sengaja
    | menunjuk ke alamat produksi supaya situs tidak terindeks sebagai dua
    | halaman berbeda saat sedang dibuka dari localhost.
    |
    | Nilainya masih bisa ditimpa lewat kolom "Alamat Situs" pada menu
    | Pengaturan LajuKasir, jadi satu database bisa dipakai untuk staging dan
    | produksi tanpa mengubah kode.
    |
    */

    'url' => env('LAJUKASIR_URL', 'https://'.env('LAJUKASIR_HOST', 'lajukasir.digipro-solution.com')),

    /*
    |--------------------------------------------------------------------------
    | Semua alamat halaman LajuKasir
    |--------------------------------------------------------------------------
    |
    | Alamat resminya selalu di urutan pertama, sedangkan alias pengembangan
    | menyusul. routes/web.php memakainya untuk Route::domain(), dan
    | VisitorRecorder memakainya untuk menyatukan kunjungan dari alias ke dalam
    | satu hitungan dengan subdomain sebenarnya, supaya panel analitik tetap
    | memisahkan LajuKasir dari DigiPro Solution.
    |
    */

    'hosts' => array_values(array_unique(array_filter([
        env('LAJUKASIR_HOST', 'lajukasir.digipro-solution.com'),
        env('LAJUKASIR_LOCAL_HOST', 'lajukasir.localhost'),
    ]))),

    /*
    |--------------------------------------------------------------------------
    | Alamat pratinjau lokal
    |--------------------------------------------------------------------------
    |
    | Path tambahan di domain utama yang menyajikan halaman LajuKasir, supaya
    | admin bisa memeriksa isinya tanpa menyetel subdomain. Halamannya diberi
    | penanda noindex dan kunjungannya tidak ikut dicatat di analitik.
    |
    */

    'preview_path' => env('LAJUKASIR_PREVIEW_PATH', 'lajukasir-preview'),

    /*
    |--------------------------------------------------------------------------
    | Pratinjau di domain utama
    |--------------------------------------------------------------------------
    |
    | Matikan dengan LAJUKASIR_PREVIEW=false untuk menghilangkan alamat
    | pratinjau sama sekali, misalnya setelah halamannya pindah ke subdomain
    | yang sebenarnya.
    |
    */

    'preview_enabled' => (bool) env('LAJUKASIR_PREVIEW', true),

];
