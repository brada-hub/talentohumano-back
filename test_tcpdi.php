<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use iio\libmergepdf\Merger;
use iio\libmergepdf\Driver\TcpdiDriver;

$repo = app()->make(\Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface::class);
$employees = $repo->findAllActive();
$idPersona = $employees[0]['persona']['id'];
$details = $repo->findPersonaCvDetails($idPersona);
$adjuntos = $repo->getAttachments($details);

$merger = new Merger(new TcpdiDriver);

foreach($adjuntos as $adj) {
    if ($adj['type'] === 'pdf') {
        if (file_exists($adj['path'])) {
            echo "Añadiendo a Merger: " . $adj['path'] . "\n";
            try {
                $merger->addFile($adj['path']);
            } catch (\Throwable $e) {
                echo "-> FAIL to add to merger : " . $e->getMessage() . "\n";
            }
        }
    }
}

try {
    $out = $merger->merge();
    echo "\n\nMERGER SUCCESS: String length: " . strlen($out) . "\n";
} catch (\Throwable $e) {
    echo "\n\nMERGER FAIL: " . $e->getMessage() . "\n";
}
