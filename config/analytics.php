<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Zona waktu untuk angka harian di dasbor
    |--------------------------------------------------------------------------
    |
    | Waktu kejadian tetap disimpan dalam UTC, seperti biasa. Kolom visited_on
    | (lihat tabel analytics_events) yang menentukan masuk hari mana sebuah
    | kunjungan, dan kolom itu diisi memakai zona waktu di bawah ini.
    |
    | Jadi "pengunjung hari ini" berganti di tengah malam waktu Indonesia,
    | bukan pukul 07.00 pagi seperti kalau memakai UTC.
    |
    */

    'timezone' => env('ANALYTICS_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Rentang grafik
    |--------------------------------------------------------------------------
    |
    | Berapa hari yang ditampilkan di kartu ringkasan dan grafik tren.
    |
    */

    'days' => (int) env('ANALYTICS_DAYS', 30),

];
