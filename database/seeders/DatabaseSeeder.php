<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Akun demo bawaan seeder lama. Kata sandinya adalah kata sandi bawaan
     * Laravel, jadi akun ini tidak boleh dibiarkan hidup di server mana pun.
     */
    protected const LEGACY_DEMO_EMAIL = 'test@example.com';

    /**
     * Kata sandi bawaan Laravel untuk akun demo.
     */
    protected const LEGACY_DEMO_PASSWORD = 'password';

    /**
     * Batas panjang kata sandi admin terpendek.
     */
    protected const MIN_PASSWORD_LENGTH = 12;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createAdminUser();
        $this->disableLegacyDemoAccount();

        // Isi awal halaman LajuKasir: fitur unggulan, cara mulai dan tanya
        // jawab. Semuanya bisa diubah lagi dari menu grup "LajuKasir".
        $this->call(LajukasirContentSeeder::class);
    }

    /**
     * Membuat akun admin dari ADMIN_EMAIL dan ADMIN_PASSWORD di berkas .env.
     *
     * Tidak ada akun dengan kata sandi bawaan: selama kedua nilai itu belum
     * diisi, seeder tidak membuat akun apa pun, sehingga tidak mungkin ada akun
     * dengan kata sandi yang bisa ditebak orang lain.
     */
    protected function createAdminUser(): void
    {
        $email = mb_strtolower(trim((string) config('security.seed_admin_email')));
        $password = (string) config('security.seed_admin_password');

        if ($email === '' && $password === '') {
            $this->warn('ADMIN_EMAIL dan ADMIN_PASSWORD belum diisi, jadi tidak ada akun admin yang dibuat.');

            return;
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->warn('ADMIN_EMAIL belum diisi atau bukan alamat email yang sah, jadi tidak ada akun admin yang dibuat.');

            return;
        }

        if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
            $this->warn('Kata sandi admin minimal '.self::MIN_PASSWORD_LENGTH.' karakter, jadi tidak ada akun admin yang dibuat.');

            return;
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = filled($user->name) ? $user->name : Str::before($email, '@');
        // Cast "hashed" pada model User yang mengenkripsi nilainya.
        $user->password = $password;
        $user->email_verified_at ??= now();
        $user->save();

        $this->info('Akun admin '.$email.' sudah siap dipakai login.');
    }

    /**
     * Mematikan akun demo bawaan seeder lama.
     *
     * Akun test@example.com dengan kata sandi "password" berasal dari versi
     * awal proyek ini. Kalau basis datanya disalin dari pengembangan ke
     * hosting, akun itu ikut terbawa dan menjadi pintu masuk yang terbuka
     * lebar. Kata sandinya diganti dengan nilai acak yang tidak pernah
     * ditampilkan, sehingga akunnya tidak bisa dipakai lagi.
     */
    protected function disableLegacyDemoAccount(): void
    {
        $user = User::query()->where('email', self::LEGACY_DEMO_EMAIL)->first();

        if (! $user instanceof User) {
            return;
        }

        if (! Hash::check(self::LEGACY_DEMO_PASSWORD, (string) $user->password)) {
            $this->warn('Akun '.self::LEGACY_DEMO_EMAIL.' sudah memakai kata sandi sendiri. Periksa apakah akun itu memang masih diperlukan.');

            return;
        }

        $user->password = Str::password(32);
        $user->save();

        $this->warn('Akun '.self::LEGACY_DEMO_EMAIL.' memakai kata sandi bawaan, jadi kata sandinya sudah diganti dengan yang acak. Akun itu tidak bisa dipakai login lagi; hapuslah kalau memang tidak diperlukan.');
    }

    /**
     * Menulis pesan peringatan ke layar pemanggil seeder, kalau ada.
     */
    protected function warn(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
    }

    /**
     * Menulis pesan keterangan ke layar pemanggil seeder, kalau ada.
     */
    protected function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }
}
