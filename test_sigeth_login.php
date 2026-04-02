<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$repo = app(\Src\Auth\Domain\Repositories\UserRepositoryInterface::class);
$user = $repo->findByUsername('13260003');
echo json_encode($user->toArray(), JSON_PRETTY_PRINT);
