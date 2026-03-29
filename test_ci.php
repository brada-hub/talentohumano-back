<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Ver cómo está exactamente el CI en la BD
$rows = DB::select("SELECT id, ci, fecha_nacimiento FROM personas WHERE ci LIKE '132600%' LIMIT 5");
echo "=== CIs que empiezan con 132600 ===\n";
foreach ($rows as $r) {
    echo "ID: {$r->id} | CI:[{$r->ci}] | FN:[{$r->fecha_nacimiento}]\n";
    echo "  ci hex: " . bin2hex($r->ci) . "\n";
}

// Probar la misma query del repositorio
echo "\n=== Test query LIKE ===\n";
$ci = '13260003';
$rows2 = DB::select("SELECT id, ci FROM personas WHERE ci = ? OR ci LIKE ? OR REPLACE(ci,' ','') LIKE ?",
    [$ci, $ci . ' %', strtoupper(str_replace(' ', '', $ci)) . '%']);
echo "Resultados con LIKE: " . count($rows2) . "\n";
foreach ($rows2 as $r) {
    echo "  CI encontrado: [{$r->ci}]\n";
}
