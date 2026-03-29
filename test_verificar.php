<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = Illuminate\Http\Request::create('/api/portal/verificar', 'POST', [
    'ci' => '13260003',
    'fecha_nacimiento' => '2004-05-06'
]);

$response = $app->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . substr($response->getContent(), 0, 500) . "\n";
