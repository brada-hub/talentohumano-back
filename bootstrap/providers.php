<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    Src\Auth\Infrastructure\Providers\AuthServiceProvider::class,
    Src\Geo\Infrastructure\Providers\GeoServiceProvider::class,
    Src\TalentoHumano\Infrastructure\Providers\TalentoHumanoServiceProvider::class,
    Src\Academico\Infrastructure\Providers\AcademicoServiceProvider::class,
    Src\Beneficios\Infrastructure\Providers\BeneficiosServiceProvider::class,
];
