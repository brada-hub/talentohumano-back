<?php

use Illuminate\Support\Facades\Route;

// Auth Routes
require __DIR__ . '/../src/Auth/Infrastructure/Http/Routes/auth.routes.php';

// Geo Routes
require __DIR__ . '/../src/Geo/Infrastructure/Http/Routes/geo.routes.php';

// Talento Humano Routes
require __DIR__ . '/../src/TalentoHumano/Infrastructure/Http/Routes/talento_humano.routes.php';

// Beneficios Routes
require __DIR__ . '/../src/Beneficios/Infrastructure/Http/Routes/beneficios.routes.php';

// Onboarding Routes
require __DIR__ . '/../src/Onboarding/Infrastructure/Http/Routes/onboarding.routes.php';

// Academico Routes
require __DIR__ . '/../src/Academico/Infrastructure/Http/Routes/academico.routes.php';

// Personal Routes
require __DIR__ . '/../src/Personal/Infrastructure/Http/Routes/personal.routes.php';

// Recordatorios Routes
require __DIR__ . '/../src/Recordatorios/Infrastructure/Http/Routes/recordatorios.routes.php';

// Reportes Routes
require __DIR__ . '/../src/Reportes/Infrastructure/Http/Routes/reportes.routes.php';
