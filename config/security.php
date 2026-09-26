<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Siapa yang boleh masuk ke panel admin
    |--------------------------------------------------------------------------
    |
    | Daftar alamat email yang boleh membuka /admin, dipisah koma lewat
    | ADMIN_EMAILS di berkas .env. Isinya dibandingkan tanpa membedakan huruf
    | besar/kecil (lihat App\Models\User::canAccessPanel()).
    |
    | Daftar yang kosong berarti setiap pemilik akun di tabel users boleh masuk,
    | yaitu perilaku lama. Karena itu daftar ini wajib diisi di server produksi:
    | tanpa daftar, satu akun lama yang kata sandinya bocor sudah cukup untuk
    | membuka seluruh isi panel.
    |
    | Contoh:
    |
    |   ADMIN_EMAILS="admin@digipro-solution.com,owner@digipro-solution.com"
    |
    */

    'admin_emails' => array_values(array_unique(array_filter(array_map(
        static fn (string $email): string => mb_strtolower(trim($email)),
        explode(',', (string) env('ADMIN_EMAILS', '')),
    )))),

    /*
    |--------------------------------------------------------------------------
    | Akun admin pertama
    |--------------------------------------------------------------------------
    |
    | Dipakai database/seeders/DatabaseSeeder.php untuk membuat akun admin.
    | Kalau ADMIN_PASSWORD kosong, seeder tidak membuat akun sama sekali
    | sehingga tidak pernah ada akun dengan kata sandi yang bisa ditebak.
    |
    | Kata sandinya minimal 12 karakter; ADMIN_EMAIL dan ADMIN_PASSWORD hanya
    | dibaca saat seeding, jadi keduanya boleh dihapus lagi dari .env sesudahnya.
    |
    */

    'seed_admin_email' => env('ADMIN_EMAIL'),

    'seed_admin_password' => env('ADMIN_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Batas ukuran unggahan berkas
    |--------------------------------------------------------------------------
    |
    | Dalam kilobyte, berlaku untuk semua kolom unggah berkas di panel admin
    | (lihat App\Providers\Filament\AdminPanelProvider). Tanpa batas ini sebuah
    | berkas raksasa bisa memenuhi ruang penyimpanan hosting.
    |
    */

    'max_upload_size' => (int) env('MAX_UPLOAD_SIZE', 8192),

    /*
    |--------------------------------------------------------------------------
    | Batas pencatatan kunjungan per alamat IP
    |--------------------------------------------------------------------------
    |
    | Berapa kunjungan halaman yang dicatat analitik untuk satu alamat IP dalam
    | satu menit. Halamannya tetap dilayani seperti biasa setelah batasnya
    | tercapai, hanya pencatatannya yang berhenti, sehingga tabel analitik tidak
    | bisa dibanjiri oleh satu pengirim saja.
    |
    */

    'max_recorded_views_per_minute' => (int) env('ANALYTICS_MAX_VIEWS_PER_MINUTE', 60),

    /*
    |--------------------------------------------------------------------------
    | Paksa HTTPS
    |--------------------------------------------------------------------------
    |
    | Hidupkan (FORCE_HTTPS=true) setelah sertifikat SSL terpasang, supaya semua
    | alamat yang dibuat Laravel memakai https dan cookie sesi bisa dikirim
    | dengan aman (SESSION_SECURE_COOKIE=true).
    |
    | Kalau servernya berada di belakang proxy atau Cloudflare, isi juga daftar
    | proxy yang boleh dipercaya lewat TRUSTED_PROXIES di berkas .env, kalau
    | tidak, https-nya tidak terdeteksi.
    |
    */

    'force_https' => (bool) env('FORCE_HTTPS', false),

];
