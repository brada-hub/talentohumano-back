<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $handler = $app->make(\Src\TalentoHumano\Application\Empleados\GenerateCvPdfHandler::class);
    $result = $handler->handle('22414e17-961c-4bbc-9ab1-780cf9b2232c');
    echo 'OK ' . strlen($result['pdf_binary']) . PHP_EOL;
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
