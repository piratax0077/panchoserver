<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("DESCRIBE vale_mercaderia");
echo "\n=== Estructura de vale_mercaderia ===\n";
foreach ($columns as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key}\n";
}

echo "\n=== Estructura de repuestos ===\n";
$columns2 = DB::select("DESCRIBE repuestos");
foreach ($columns2 as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key}\n";
}

echo "\n=== Estructura de users ===\n";
$columns3 = DB::select("DESCRIBE users");
foreach ($columns3 as $col) {
    echo "Field: {$col->Field} | Type: {$col->Type} | Null: {$col->Null} | Key: {$col->Key}\n";
}
