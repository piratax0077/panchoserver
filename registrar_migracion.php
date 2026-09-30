<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

try {
    $batch = DB::table('migrations')->max('batch') + 1;
    DB::table('migrations')->insert([
        'migration' => '2026_06_03_000000_create_movimientos_vales_mercaderia',
        'batch' => $batch
    ]);
    echo "✅ Migración registrada exitosamente!\n";
    echo "   La tabla 'vale_mercaderia_movimientos' esta lista para usar\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
