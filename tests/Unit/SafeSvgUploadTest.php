<?php

namespace Tests\Unit;

use App\Rules\SafeSvgUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pemeriksaan isi berkas SVG pada unggahan panel admin.
 *
 * Berkas SVG bisa memuat skrip yang berjalan di browser pengunjung begitu
 * tautannya dibuka langsung, jadi bentuk-bentuk berbahaya di bawah ini harus
 * ditolak sebelum tersimpan.
 */
class SafeSvgUploadTest extends TestCase
{
    /**
     * Isi SVG yang ditolak.
     *
     * @return array<string, array{0: string}>
     */
    public static function dangerousSvgs(): array
    {
        return [
            'elemen script' => ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.cookie)</script></svg>'],
            'penangan kejadian onload' => ['<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'],
            'penangan kejadian onerror pada gambar' => ['<svg xmlns="http://www.w3.org/2000/svg"><image href="x" onerror="alert(1)" /></svg>'],
            'tautan javascript' => ['<svg xmlns="http://www.w3.org/2000/svg"><a href="javascript:alert(1)"><text>Klik</text></a></svg>'],
            'iframe' => ['<svg xmlns="http://www.w3.org/2000/svg"><foreignObject><iframe src="https://jahat.example"></iframe></foreignObject></svg>'],
            'foreignObject' => ['<svg xmlns="http://www.w3.org/2000/svg"><foreignObject><body xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></body></foreignObject></svg>'],
            'entitas XML' => ['<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg"><text>&xxe;</text></svg>'],
            'animation yang mengubah penangan kejadian' => ['<svg xmlns="http://www.w3.org/2000/svg"><set attributeName="onload" to="alert(1)" /></svg>'],
            'rujukan ke alamat luar' => ['<svg xmlns="http://www.w3.org/2000/svg"><use href="https://jahat.example/sprite.svg#x" /></svg>'],
            'data text/html' => ['<svg xmlns="http://www.w3.org/2000/svg"><image href="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==" /></svg>'],
            'halaman HTML yang diberi nama svg' => ['<html><body><script>alert(1)</script></body></html>'],
        ];
    }

    /**
     * Isi SVG yang tetap diterima.
     *
     * @return array<string, array{0: string}>
     */
    public static function safeSvgs(): array
    {
        return [
            'svg sederhana' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 12"><rect width="40" height="12" fill="#087b6a" /></svg>'],
            'dokumen svg lengkap' => ["<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"40\" height=\"12\"><text x=\"0\" y=\"10\">LajuKasir</text></svg>"],
            'gradasi dan gaya' => ['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 12"><style>.a{fill:#1b2a3b}</style><rect class="a" width="40" height="12" /><animate attributeName="opacity" values="1;0.5;1" dur="2s" /></svg>'],
            'rujukan ke simbol di berkas yang sama' => ['<svg xmlns="http://www.w3.org/2000/svg"><defs><g id="tanda"><circle r="4" /></g></defs><use href="#tanda" /></svg>'],
            'tulisan yang memuat kata on diikuti tanda sama dengan' => ['<svg xmlns="http://www.w3.org/2000/svg"><text x="0" y="10">Buka menu on = pengaturan</text></svg>'],
        ];
    }

    #[DataProvider('dangerousSvgs')]
    public function test_an_svg_that_can_run_code_is_rejected(string $contents): void
    {
        $this->assertFalse($this->passes('logo.svg', $contents));
    }

    #[DataProvider('safeSvgs')]
    public function test_an_ordinary_svg_is_accepted(string $contents): void
    {
        $this->assertTrue($this->passes('logo.svg', $contents));
    }

    public function test_a_png_file_is_not_inspected_as_an_svg(): void
    {
        // Berkas gambar biasa tidak bisa memuat skrip, jadi isinya tidak dibaca.
        $this->assertTrue($this->passes('logo.png', '<script>alert(1)</script>'));
    }

    public function test_a_file_name_that_only_ends_with_svg_is_still_inspected(): void
    {
        $this->assertFalse($this->passes('Logo.SVG', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'));
    }

    /**
     * Skrip yang disembunyikan jauh di dalam berkas tetap ditemukan, walaupun
     * isinya dibaca sepotong-sepotong.
     */
    public function test_a_script_hidden_behind_a_large_padding_is_found(): void
    {
        $padding = str_repeat('<!-- '.str_repeat('x', 900).' -->', 200);

        $this->assertFalse($this->passes('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg">'.$padding.'<script>alert(1)</script></svg>'));
    }

    /**
     * Skrip yang tepat berada di perbatasan potongan pembacaan juga ditemukan.
     */
    public function test_a_script_on_a_chunk_boundary_is_found(): void
    {
        // 65536 byte adalah ukuran satu potongan pembacaan.
        $padding = str_repeat('x', 65530);

        $this->assertFalse($this->passes('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><!-- '.$padding.' --><script>alert(1)</script></svg>'));
    }

    public function test_an_svg_larger_than_the_scan_limit_is_refused(): void
    {
        // Berkas sebesar ini tidak bisa diperiksa seluruhnya, jadi ditolak
        // walaupun isinya belum tentu berbahaya.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">'.str_repeat('x', 8388609).'</svg>';

        $this->assertFalse($this->passes('logo.svg', $svg));
    }

    private function passes(string $name, string $contents): bool
    {
        $validator = Validator::make(
            ['logo' => UploadedFile::fake()->createWithContent($name, $contents)],
            ['logo' => [new SafeSvgUpload]],
        );

        return $validator->passes();
    }
}
