<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$repo = app(\Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface::class);
$result = $repo->getResumen([]);
var_export($result['cumpleanios']);
