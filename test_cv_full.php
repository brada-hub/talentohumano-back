<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test completo de generación de CV con respaldos ===\n\n";

$handler = app()->make(\Src\TalentoHumano\Application\Empleados\GenerateCvPdfHandler::class);

// Buscar el ID de persona del primer empleado activo
$repo = app()->make(\Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface::class);
$employees = $repo->findAllActive();
$idPersona = $employees[0]['persona']['id'];
echo "Persona ID: $idPersona\n";

try {
    $result = $handler->handle($idPersona);
    $size = strlen($result['pdf_binary']);
    echo "✅ PDF generado exitosamente!\n";
    echo "   Nombre: {$result['filename']}\n";
    echo "   Tamaño: " . number_format($size / 1024, 1) . " KB\n";
    
    // Guardar para verificar
    file_put_contents(storage_path('app/cv_test_output.pdf'), $result['pdf_binary']);
    echo "   Guardado en: storage/app/cv_test_output.pdf\n";
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
