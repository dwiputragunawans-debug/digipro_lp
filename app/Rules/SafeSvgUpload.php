<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

/**
 * Menolak berkas SVG yang bisa menjalankan kode di browser pengunjung.
 *
 * Sebuah berkas SVG adalah dokumen yang bisa memuat <script> dan penangan
 * kejadian seperti onload. Berkas seperti itu belum berbahaya selama ditampilkan
 * lewat <img> atau sebagai masker CSS, tetapi begitu tautannya dibuka langsung
 * dari /storage/…, skripnya berjalan di alamat situs ini juga (stored XSS) —
 * termasuk membaca cookie sesi panel admin.
 *
 * Karena itu setiap berkas .svg yang diunggah dari panel admin diperiksa
 * isinya lebih dahulu. Berkas gambar biasa (PNG, JPG, WebP, GIF) tidak
 * diperiksa karena tipe itu tidak bisa memuat skrip.
 *
 * Pemeriksaan ini dipasang otomatis untuk semua kolom unggah di panel admin,
 * lihat App\Providers\Filament\AdminPanelProvider.
 */
class SafeSvgUpload implements ValidationRule
{
    /**
     * Batas ukuran berkas yang masih bisa diperiksa seluruh isinya, dalam byte.
     * Nilainya sama dengan batas unggahan bawaan (MAX_UPLOAD_SIZE), karena
     * berkas yang lebih besar daripada ini sudah ditolak aturan ukuran.
     */
    protected const MAX_SCAN_BYTES = 8388608;

    /**
     * Ukuran potongan pembacaan, dan bagian akhir potongan yang dibaca ulang
     * pada potongan berikutnya. Pembacaan bertahap dipakai supaya berkas besar
     * tidak perlu dimuat seluruhnya ke memori, sedangkan tumpang tindihnya
     * menjaga agar pola di perbatasan potongan tetap ikut terbaca.
     */
    protected const CHUNK_BYTES = 65536;

    protected const OVERLAP_BYTES = 1024;

    /**
     * Ciri isi SVG yang bisa menjalankan kode atau memuat isi dari luar:
     * elemen skrip, penangan kejadian, protokol javascript:, entitas XML
     * (XXE / billion laughs) dan rujukan ke alamat luar.
     *
     * @var list<string>
     */
    protected const DANGEROUS_PATTERNS = [
        '/<script\b/i',
        '/<iframe\b/i',
        '/<embed\b/i',
        '/<object\b/i',
        '/<foreignObject\b/i',
        '/<use\b[^>]*(href|xlink:href)\s*=\s*["\']?\s*(https?:|\/\/)/i',
        '/<!ENTITY\b/i',
        '/\bjavascript\s*:/i',
        '/\bon[a-z]{3,}\s*=/i',
        '/\battributeName\s*=\s*["\']\s*on/i',
        '/data\s*:\s*text\/html/i',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach (Arr::wrap($value) as $file) {
            if (! $file instanceof UploadedFile || ! $this->isSvg($file)) {
                continue;
            }

            if ($file->getSize() > self::MAX_SCAN_BYTES) {
                $fail('Berkas SVG ini terlalu besar untuk diperiksa keamanannya, jadi tidak bisa dipakai. Simpan gambarnya sebagai PNG lebih dahulu.');

                return;
            }

            if (! $this->isClean($file)) {
                $fail('Berkas SVG ini memuat skrip atau penangan kejadian, sehingga bisa menjalankan kode di browser pengunjung. Simpan gambarnya sebagai PNG lebih dahulu, atau bersihkan skripnya.');

                return;
            }
        }
    }

    /**
     * Berkas SVG dikenali dari akhiran namanya, karena itulah yang menentukan
     * bagaimana browser memprosesnya saat berkasnya dibuka langsung.
     */
    protected function isSvg(UploadedFile $file): bool
    {
        return in_array(mb_strtolower($file->getClientOriginalExtension()), ['svg', 'svgz'], true);
    }

    /**
     * Berkas yang tidak bisa dibaca dianggap bersih: kolom unggah punya aturan
     * lain (image, mimetypes, ukuran) yang menanganinya.
     */
    protected function isClean(UploadedFile $file): bool
    {
        $path = $file->getRealPath();

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            return true;
        }

        $handle = @fopen($path, 'rb');

        if (! is_resource($handle)) {
            return true;
        }

        $foundSvgRoot = false;
        $tail = '';

        while (! feof($handle)) {
            $chunk = (string) fread($handle, self::CHUNK_BYTES);

            if ($chunk === '') {
                break;
            }

            // Pola juga dicari pada gabungan akhir potongan sebelumnya dengan
            // awal potongan ini, supaya tidak bisa disembunyikan di perbatasan.
            $window = $tail.$chunk;

            if (! $foundSvgRoot && str_contains(mb_strtolower($window), '<svg')) {
                $foundSvgRoot = true;
            }

            foreach (self::DANGEROUS_PATTERNS as $pattern) {
                if (preg_match($pattern, $window) === 1) {
                    fclose($handle);

                    return false;
                }
            }

            $tail = substr($chunk, -self::OVERLAP_BYTES);
        }

        fclose($handle);

        // Berkas berakhiran .svg yang isinya bukan SVG (misalnya halaman HTML
        // yang diberi nama .svg) juga tidak boleh diterima.
        return $foundSvgRoot;
    }
}
