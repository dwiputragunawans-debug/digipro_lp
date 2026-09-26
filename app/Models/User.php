<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Siapa yang boleh membuka panel admin.
     *
     * Kontrak FilamentUser ini wajib dipenuhi: kalau model User tidak
     * mengimplementasikannya, Filament hanya mengizinkan pemilik akun masuk
     * selama APP_ENV bernilai "local" — artinya panelnya tidak bisa dibuka sama
     * sekali di produksi (HTTP 403). Sebaliknya, tanpa daftar email di bawah,
     * setiap akun di tabel users otomatis boleh masuk.
     *
     * Isi daftar emailnya lewat ADMIN_EMAILS di berkas .env
     * (lihat config/security.php).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        $allowed = config('security.admin_emails', []);

        // Daftar kosong berarti pembatasan tidak dipakai: perilaku lama tetap
        // berjalan, tetapi daftarnya sebaiknya selalu diisi di produksi.
        if (blank($allowed)) {
            return true;
        }

        return in_array(mb_strtolower((string) $this->email), $allowed, true);
    }
}
