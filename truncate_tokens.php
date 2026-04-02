<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::table('personal_access_tokens')->truncate();
echo "TABLA DE TOKENS TRUNCADA CORRECTAMENTE. TODOS LOS SISTEMAS REQUERIRÁN LOGIN.\n";
