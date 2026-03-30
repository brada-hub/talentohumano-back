<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$personas = \Illuminate\Support\Facades\DB::table('personas')->get();
echo "PERSONAS EN BD (" . count($personas) . "):\n";
foreach($personas as $p) {
    if (isset($p->ci)) {
        echo "- ID: {$p->id}, CI: {$p->ci}, NOMBRE: " . ($p->primer_apellido ?? '') . " " . ($p->nombres ?? '') . "\n";
    }
}
