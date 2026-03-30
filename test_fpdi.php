<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = app()->make(\Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface::class);
$employees = $repo->findAllActive();
if (empty($employees)) {
    die("No active employees found in DB.");
}

$idPersona = $employees[0]['persona']['id']; // Or $employees[0]['id_persona'];
echo "Testing with persona ID: $idPersona\n";

$details = $repo->findPersonaCvDetails($idPersona);
$adjuntos = $repo->getAttachments($details);

echo "Total adjuntos: " . count($adjuntos) . "\n";

foreach($adjuntos as $adj) {
    if ($adj['type'] === 'pdf') {
        echo "\nPATH PDF: " . $adj['path'] . "\n";
        if (file_exists($adj['path'])) {
            echo "-> FILE EXISTS\n";
            try {
                $fpdi = new \setasign\Fpdi\Tcpdf\Fpdi();
                $fpdi->setSourceFile($adj['path']);
                echo "-> FPDI SUCCESS\n";
            } catch (\Throwable $e) {
                echo "-> FPDI ERROR: " . $e->getMessage() . "\n";
            }
        } else {
            echo "-> NO EXISTE EL ARCHIVO (FILE NOT FOUND)\n";
        }
    }
}
