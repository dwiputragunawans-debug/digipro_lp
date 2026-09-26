<?php

/**
 * Skrip untuk memindahkan SELURUH data dari database SQLite ke database MySQL.
 *
 * Pemakaian:
 *   php scripts/copy_sqlite_to_mysql.php
 *   php scripts/copy_sqlite_to_mysql.php --sqlite=database/database.sqlite
 *   php scripts/copy_sqlite_to_mysql.php --dry-run
 *
 * Cara kerja:
 *   1. Membaca semua tabel dari file SQLite (tabel internal sqlite_* dilewati).
 *   2. Tabel yang tidak ada di MySQL akan dilaporkan dan dilewati
 *      (jalankan `php artisan migrate` terlebih dahulu).
 *   3. Untuk setiap tabel: tabel target di MySQL dikosongkan (TRUNCATE),
 *      lalu seluruh baris dari SQLite disalin (ID/primary key tetap sama).
 *   4. Khusus tabel `migrations` TIDAK di-truncate, melainkan memakai
 *      INSERT IGNORE, supaya status migrasi MySQL tetap konsisten
 *      (mis. migrasi yang sudah dijalankan di MySQL tetapi belum ada di SQLite).
 */

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$options = getopt('', ['sqlite::', 'dry-run']);

$sqlitePath = $options['sqlite'] ?? database_path('database.sqlite');
$sqlitePath = realpath($sqlitePath) ?: $sqlitePath;
$dryRun = array_key_exists('dry-run', $options);

/** Koneksi MySQL tujuan (koneksi default aplikasi). */
$target = DB::connection('mysql');

echo "Sumber SQLite : {$sqlitePath}\n";
echo "Tujuan MySQL  : {$target->getDatabaseName()}@{$target->getConfig('host')}:{$target->getConfig('port')}\n";
echo str_repeat('-', 72) . "\n";

if (! is_file($sqlitePath)) {
    fwrite(STDERR, "File SQLite tidak ditemukan: {$sqlitePath}\n");
    exit(1);
}

$source = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tables = $source
    ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

$summary = [];
$skipped = [];

$target->statement('SET FOREIGN_KEY_CHECKS = 0');

foreach ($tables as $table) {
    if (! Schema::connection('mysql')->hasTable($table)) {
        $skipped[] = $table;
        continue;
    }

    $targetColumns = Schema::connection('mysql')->getColumnListing($table);
    $sourceColumns = array_column($source->query("PRAGMA table_info(\"{$table}\")")->fetchAll(), 'name');

    // Hanya kolom yang ada di kedua sisi (aman kalau skema sempat berubah).
    $columns = array_values(array_intersect($sourceColumns, $targetColumns));

    if ($columns === []) {
        $skipped[] = $table;
        continue;
    }

    $select = implode(', ', array_map(fn ($column) => "\"{$column}\"", $columns));
    $rows = $source->query("SELECT {$select} FROM \"{$table}\"")->fetchAll();

    if ($dryRun) {
        $summary[$table] = [count($rows), count($rows)];
        continue;
    }

    $isMigrations = $table === 'migrations';

    if (! $isMigrations) {
        $target->table($table)->truncate();
    }

    foreach (array_chunk($rows, 100) as $chunk) {
        $payload = array_map(
            fn (array $row) => array_map(
                fn ($value) => is_bool($value) ? (int) $value : $value,
                $row
            ),
            $chunk
        );

        $isMigrations
            ? $target->table($table)->insertOrIgnore($payload)
            : $target->table($table)->insert($payload);
    }

    $copied = $target->table($table)->count();
    $summary[$table] = [count($rows), $copied];
}

$target->statement('SET FOREIGN_KEY_CHECKS = 1');

echo sprintf("%-28s %10s %10s  %s\n", 'TABEL', 'SQLITE', 'MYSQL', 'STATUS');
echo str_repeat('-', 72) . "\n";

$ok = true;

foreach ($summary as $table => [$sourceCount, $targetCount]) {
    $status = ($targetCount >= $sourceCount) ? 'OK' : 'TIDAK LENGKAP';
    $ok = $ok && $targetCount >= $sourceCount;

    echo sprintf("%-28s %10d %10d  %s\n", $table, $sourceCount, $targetCount, $status);
}

foreach ($skipped as $table) {
    echo sprintf("%-28s %10s %10s  %s\n", $table, '-', '-', 'TABEL TIDAK ADA DI MYSQL (dilewati)');
}

echo str_repeat('-', 72) . "\n";
echo $dryRun
    ? "Dry run selesai, tidak ada perubahan yang ditulis.\n"
    : ($ok ? "Selesai: seluruh data berhasil dipindahkan.\n" : "PERHATIAN: ada tabel yang jumlah barisnya tidak sama.\n");

exit($ok ? 0 : 1);
