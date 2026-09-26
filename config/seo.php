<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alamat kanonis situs
    |--------------------------------------------------------------------------
    |
    | Dipakai untuk tag canonical, og:url, sitemap.xml dan robots.txt. Isi
    | SITE_URL di berkas .env dengan domain produksi, misalnya
    | https://digipro-solution.com, supaya mesin pencari selalu diarahkan ke
    | alamat yang benar walau situsnya sedang dibuka dari localhost.
    |
    | Nilainya masih bisa ditimpa lewat kolom "Alamat Situs" pada menu
    | Pengaturan Situs, jadi satu database bisa dipakai untuk staging dan
    | produksi tanpa mengubah kode.
    |
    */

    'url' => env('SITE_URL', env('APP_URL', 'http://localhost')),

];
