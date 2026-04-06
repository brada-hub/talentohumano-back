<?php

namespace Src\Recordatorios\Domain\Repositories;

interface RecordatorioRepositoryInterface
{
    public function getResumen(array $filters = []): array;

    public function sendCumpleanios(int $empleadoId, bool $automatico = false, bool $force = false): array;

    public function sendCumpleaniosDelDia(): array;
}
