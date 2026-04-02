<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$systems = \Src\Auth\Infrastructure\Persistence\Models\SistemaModel::all();
foreach ($systems as $system) {
    echo "ID: {$system->id_sistema} - Name: {$system->sistema} - Slug: " . strtolower(str_replace(' ', '_', $system->sistema)) . "\n";
}
