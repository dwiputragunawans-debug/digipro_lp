# Catatan Keamanan

Berkas ini merangkum hasil pemeriksaan keamanan landing page DigiPro Solution
dan LajuKasir, perbaikan yang sudah dikerjakan, serta hal-hal yang wajib
dilakukan sebelum situs dipasang di hosting.

Terakhir diperiksa: 26 September 2026.

## 1. Yang wajib dilakukan sebelum upload

Periksa satu per satu; nomor di bawah ini mengikuti urutan pentingnya.

1. **APP_KEY dibuat di server**, jangan menyalin berkas `.env` dari komputer
   sendiri. Kunci yang bocor membuat cookie dan sesi login bisa dipalsukan.

   ```bash
   php artisan key:generate
   ```

2. **Mode produksi dinyalakan** di `.env`:

   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://digipro-solution.com
   LOG_LEVEL=warning
   ```

   `APP_DEBUG=true` menampilkan isi `.env`, kueri basis data dan nama berkas
   sumber kepada siapa pun yang memicu galat. Sebagai jaring kedua, nilai itu
   dimatikan paksa oleh `AppServiceProvider` selama `APP_ENV=production`.

3. **Daftar admin diisi** supaya hanya email yang benar-benar dipakai yang bisa
   membuka `/admin`:

   ```
   ADMIN_EMAILS="admin@digipro-solution.com"
   ```

   Daftar kosong berarti **semua** akun di tabel `users` boleh masuk panel.

4. **Akun admin dibuat lewat seeder**, bukan dengan kata sandi bawaan:

   ```bash
   php artisan db:seed --class=DatabaseSeeder
   ```

   Seeder hanya membuat akun kalau `ADMIN_EMAIL` dan `ADMIN_PASSWORD` (minimal 12
   karakter) diisi, dan menghapus kemampuan login akun demo lama
   `test@example.com`. Sesudah akunnya jadi, kedua baris itu boleh dihapus dari
   `.env`.

   Akun yang sekarang ada di basis data pengembangan bernama
   `admin@admin.com`. Alamat seperti itu jangan dipakai di produksi; buat akun
   baru dengan alamat email sungguhan lewat `ADMIN_EMAIL`/`ADMIN_PASSWORD`, atau
   ganti dulu kata sandi akun lama:

   ```bash
   php artisan tinker
   >>> $u = App\Models\User::where('email', 'admin@admin.com')->first();
   >>> $u->password = 'kata-sandi-baru-yang-panjang';
   >>> $u->save();
   ```

   Jangan lupa akunnya juga dimasukkan ke `ADMIN_EMAILS` pada langkah 3.

5. **Cookie sesi dikunci ke https** setelah sertifikat SSL terpasang:

   ```
   SESSION_SECURE_COOKIE=true
   SESSION_ENCRYPT=true
   FORCE_HTTPS=true
   ```

   Kalau hostingnya memakai proxy atau Cloudflare, isi juga `TRUSTED_PROXIES`
   dengan alamat proxy-nya, kalau tidak, https tidak terdeteksi.

6. **Pratinjau lokal dimatikan** kalau halaman LajuKasir sudah dilayani dari
   subdomainnya sendiri:

   ```
   LAJUKASIR_PREVIEW=false
   ```

7. **Akun basis data bukan root** dan memakai kata sandi:

   ```
   DB_USERNAME=digipro_user
   DB_PASSWORD=…
   ```

8. **Document root diarahkan ke folder `public/`.** Kalau hosting tidak
   mengizinkan hal itu, berkas `.htaccess` di akar proyek sudah menyiapkan
   pengamanannya: seluruh permintaan diarahkan ke `public/` dan daftar isi
   folder dimatikan, sehingga `.env`, `database/` dan `vendor/` tidak bisa
   diunduh.

9. **Berkas yang tidak boleh ikut diunggah**: `.env` milik komputer sendiri,
   `database/database.sqlite`, `database/database.sqlite.backup-*`,
   `node_modules/`, `tests/`, `.git/`. Yang memang perlu ada di server:
   `storage/` (dengan izin tulis) dan tautan `public/storage`.

   ```bash
   php artisan storage:link
   ```

   Tautan `public/storage` di komputer pengembang menunjuk ke alamat lengkap
   (`/Users/…`), jadi jangan disalin apa adanya — hapus dulu di server, baru
   jalankan perintah di atas supaya tautannya dibuat ulang sesuai alamat server.

10. **Hak akses folder** di server: `storage/` dan `bootstrap/cache/` boleh
    ditulis (755/775), sedangkan `.env` sebaiknya hanya bisa dibaca pemiliknya
    (600).

## 2. Setelah dipasang, periksa ini

```bash
# 1. Panel admin minta login, bukan terbuka begitu saja.
curl -I https://digipro-solution.com/admin        # harus 302 ke /admin/login

# 2. Header keamanan ikut terkirim.
curl -I https://digipro-solution.com/ | grep -i -E 'x-frame|x-content|referrer|permissions|strict-transport'

# 3. Berkas sensitif tidak bisa diunduh (harus 403 atau 404).
curl -I https://digipro-solution.com/.env
curl -I https://digipro-solution.com/database/database.sqlite

# 4. Galat tidak menampilkan jejak kode (APP_DEBUG=false).
curl -I https://digipro-solution.com/halaman-yang-tidak-ada
```

Sesudah itu, login ke panel dan pastikan menunya bisa dibuka (kalau tidak,
biasanya `ADMIN_EMAILS` tidak cocok dengan email akunnya).

## 3. Hasil pemeriksaan per bagian

| Bagian | Keadaan | Keterangan |
| --- | --- | --- |
| Filament authorization | diperbaiki | `App\Models\User` sekarang memenuhi kontrak `FilamentUser` dengan daftar email `ADMIN_EMAILS`. Sebelumnya Filament mengunci panel di produksi (403) karena kontraknya belum dipenuhi, sedangkan tanpa daftar email setiap akun di tabel `users` bisa masuk. |
| Akun bawaan | diperbaiki | Seeder lama membuat `test@example.com` dengan kata sandi `password`. Sekarang akun admin dibuat dari `ADMIN_EMAIL`/`ADMIN_PASSWORD`, dan akun demo lama yang masih memakai kata sandi bawaan dimatikan otomatis. |
| API & IDOR | aman | Tidak ada route `api/*`. Tidak ada data yang bisa dibuka lewat id; panel hanya bisa dibuka admin yang sudah login. |
| Unggahan berkas | diperbaiki | Semua kolom unggah dibatasi 8 MB (`MAX_UPLOAD_SIZE`) dan berkas SVG diperiksa isinya: `<script>`, penangan kejadian, `javascript:`, entitas XML dan rujukan ke alamat luar ditolak (`App\Rules\SafeSvgUpload`). Nama berkas disimpan acak oleh Filament. |
| .env, APP_KEY, rahasia | diperbaiki | `.env` tidak ikut diunggah, `APP_DEBUG` dimatikan paksa di produksi, dan `.env.example` memuat daftar periksa produksi. `.htaccess` akar proyek menutup akses ke berkas sensitif. |
| SQL Injection | aman | Semua kueri lewat Eloquent/query builder dengan binding. `selectRaw()` hanya memakai ungkapan tetap (`COUNT(*)`, `MAX(id)`); tidak ada masukan pengunjung yang masuk ke SQL. |
| XSS | aman | Blade meng-escape `{{ }}`; satu-satunya keluaran mentah `{!! !!}` adalah JSON-LD yang sudah memakai `JSON_HEX_TAG`, `JSON_HEX_AMP`, `JSON_HEX_APOS`, `JSON_HEX_QUOT`. Kolom tabel Filament tidak memakai `->html()`, sehingga data dari pengunjung (halaman yang dikunjungi, asal pengunjung) selalu di-escape. |
| Login brute-force | aman | Login Filament dibatasi 5 percobaan per menit (`Filament\Pages\Auth\Login`). Tidak ada pendaftaran mandiri, reset kata sandi, atau akun tamu. |
| Session & cookie | diperbaiki | Petunjuk produksi ditambahkan di `.env.example`: `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `SESSION_SAME_SITE=lax` (bawaan). Cookie sesi `httponly` menyala. |
| CSRF | aman | Middleware `PreventRequestForgery` aktif di grup `web`; route `POST /analytics/whatsapp-click` ikut melewatinya dan laporan dari JavaScript memakai `X-CSRF-TOKEN`. |
| Rate limiting | diperbaiki | Endpoint analitik sudah `throttle:60,1`. Ditambahkan batas pencatatan kunjungan per alamat IP (`ANALYTICS_MAX_VIEWS_PER_MINUTE`) supaya tabel `analytics_events` tidak bisa dibanjiri; halamannya tetap dilayani seperti biasa. |
| Header keamanan | diperbaiki | `App\Http\Middleware\SecurityHeaders`: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, `X-Permitted-Cross-Domain-Policies`, dan `Strict-Transport-Security` (hanya lewat https). |
| Dependency | aman | `composer audit` dan `npm audit --omit=dev` tidak menemukan kerentanan yang diketahui. |
| Lain-lain | diperbaiki | `TRUSTED_PROXIES` untuk proxy/Cloudflare, tautan WhatsApp keluar sudah memakai `rel="noopener"`, `robots.txt` menutup `/admin`. |

## 4. Yang sengaja belum dipasang

- **Content-Security-Policy.** Panel Filament memakai Livewire dan Alpine yang
  memerlukan `unsafe-inline`/`unsafe-eval`, sedangkan halaman landing memakai
  gaya Tailwind di dalam tag. CSP yang ketat akan mematahkan keduanya, sedangkan
  yang longgar hampir tidak menambah perlindungan. Header lain di atas sudah
  menutup celah yang paling sering dipakai (clickjacking, penyadapan tipe
  berkas, kebocoran alamat halaman).
- **Header keamanan untuk berkas di `public/storage`.** Berkas statis dilayani
  langsung oleh web server, bukan Laravel, sehingga header dari aplikasi tidak
  ikut terkirim. Karena itu unggahan SVG diperiksa isinya sebelum tersimpan.
- **`expose_php = Off`.** Bisa diminta ke penyedia hosting supaya versi PHP
  tidak ikut terkirim di header `X-Powered-By`.
- **Penghapusan berkas cadangan SQLite.** `database/database.sqlite` dan
  `database/database.sqlite.backup-*` milik komputer pengembang masih ada di
  proyek. Berkas itu tidak boleh ikut diunggah ke hosting karena memuat hash
  kata sandi akun admin; hapus (atau simpan sebagai cadangan di luar folder
  proyek) sebelum menyalin berkasnya ke server.

## 5. Berkas yang terkait

- `config/security.php` — daftar admin, akun seeder, batas unggahan, batas
  pencatatan analitik, paksa https.
- `app/Models/User.php` — `canAccessPanel()`.
- `app/Http/Middleware/SecurityHeaders.php` — header keamanan.
- `app/Http/Middleware/TrackVisitor.php` dan
  `app/Support/Analytics/VisitorRecorder.php` — batas pencatatan kunjungan.
- `app/Providers/Filament/AdminPanelProvider.php` — batas ukuran dan pemeriksaan
  SVG untuk semua kolom unggah.
- `app/Rules/SafeSvgUpload.php` — pemeriksaan isi berkas SVG.
- `database/seeders/DatabaseSeeder.php` — akun admin dan pembersihan akun demo.
- `.env.example` — daftar periksa produksi dan nilai baru
  (`ADMIN_EMAILS`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `MAX_UPLOAD_SIZE`,
  `FORCE_HTTPS`, `TRUSTED_PROXIES`, `ANALYTICS_MAX_VIEWS_PER_MINUTE`).
- `.htaccess` (akar proyek) dan `public/.htaccess` — penjaga berkas sensitif.
- `tests/Feature/SecurityHardeningTest.php` dan `tests/Unit/SafeSvgUploadTest.php`
  — pengujiannya.

