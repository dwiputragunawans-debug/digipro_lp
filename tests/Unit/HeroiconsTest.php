<?php

namespace Tests\Unit;

use App\Support\Heroicons;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HeroiconsTest extends TestCase
{
    #[DataProvider('iconNames')]
    public function test_it_normalizes_the_icon_name_read_from_the_database(?string $value, string $expected): void
    {
        $this->assertSame($expected, Heroicons::normalize($value));
    }

    /**
     * @return array<string, array{0: string|null, 1: string}>
     */
    public static function iconNames(): array
    {
        return [
            'kosong' => [null, Heroicons::FALLBACK],
            'hanya spasi' => ['   ', Heroicons::FALLBACK],
            'nama singkat' => ['bolt', 'heroicon-o-bolt'],
            'tanpa awalan heroicon' => ['o-bolt', 'heroicon-o-bolt'],
            'nama lengkap' => ['heroicon-o-bolt', 'heroicon-o-bolt'],
            'versi solid' => ['s-bolt', 'heroicon-s-bolt'],
            'versi mini' => ['m-bolt', 'heroicon-m-bolt'],
            'nama gaya lama' => ['AcademicCapIcon', 'heroicon-o-academic-cap'],
            'nama gaya lama tanpa Icon' => ['GlobeAlt', 'heroicon-o-globe-alt'],
            'nama gaya lama dari data lama' => ['globe-alt', 'heroicon-o-globe-alt'],
            'ikon tidak ada' => ['heroicon-o-tidak-ada', Heroicons::FALLBACK],
            'teks bebas' => ['ikon-lama', Heroicons::FALLBACK],
        ];
    }

    public function test_every_icon_offered_in_the_admin_panel_exists(): void
    {
        foreach (Heroicons::options() as $icon => $label) {
            $this->assertTrue(Heroicons::exists($icon), "Ikon {$icon} tidak ada di paket blade-heroicons.");
            $this->assertNotSame('', $label, "Ikon {$icon} belum punya label.");
        }
    }

    public function test_the_admin_options_draw_the_icon_next_to_its_label(): void
    {
        $options = Heroicons::optionsWithIcons();

        $this->assertStringContainsString('<svg', $options['heroicon-o-bolt']);
        $this->assertStringContainsString('Petir, cepat (bolt)', $options['heroicon-o-bolt']);
    }

    public function test_it_only_resolves_an_icon_that_is_really_there(): void
    {
        $this->assertSame('heroicon-o-bolt', Heroicons::resolve('bolt'));
        $this->assertSame('heroicon-o-globe-alt', Heroicons::resolve('GlobeAlt'));

        // Kosong dan nilai yang tidak dikenal tidak menghasilkan ikon apa pun,
        // supaya pemanggilnya bisa memakai tampilan cadangannya sendiri.
        $this->assertNull(Heroicons::resolve(null));
        $this->assertNull(Heroicons::resolve('   '));
        $this->assertNull(Heroicons::resolve('icons1'));
        $this->assertNull(Heroicons::resolve('heroicon-o-tidak-ada'));
    }

    public function test_an_unknown_value_that_is_already_saved_stays_selectable(): void
    {
        $options = Heroicons::optionsWithIcons('ikon-lama');

        // Nilai lamanya tetap ada supaya tidak tertimpa saat form disimpan, dan
        // ikon yang digambar untuk pilihan itu adalah ikon cadangan (petir).
        $this->assertArrayHasKey('ikon-lama', $options);
        $this->assertStringContainsString('ikon-lama (tidak ada di daftar', $options['ikon-lama']);
        $this->assertStringContainsString(svg(Heroicons::FALLBACK, 'w-5 h-5 shrink-0')->toHtml(), $options['ikon-lama']);
    }
}
