<?php

namespace Src\Beneficios\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Beneficios\Application\CreateBeneficiarioHandler;
use Src\Beneficios\Application\DeleteBeneficiarioHandler;
use Src\Beneficios\Application\GetBeneficiariosByEmpleadoHandler;
use Src\Beneficios\Application\GetBeneficiosCatalogsHandler;
use Src\Beneficios\Application\UpdateBeneficiarioHandler;
use Src\Beneficios\Infrastructure\Http\Requests\CreateBeneficiarioRequest;
use Src\Beneficios\Infrastructure\Http\Requests\UpdateBeneficiarioRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;
use InvalidArgumentException;

final class BeneficiosController
{
    public function __construct(
        private readonly GetBeneficiosCatalogsHandler $getBeneficiosCatalogsHandler,
        private readonly GetBeneficiariosByEmpleadoHandler $getBeneficiariosByEmpleadoHandler,
        private readonly CreateBeneficiarioHandler $createBeneficiarioHandler,
        private readonly UpdateBeneficiarioHandler $updateBeneficiarioHandler,
        private readonly DeleteBeneficiarioHandler $deleteBeneficiarioHandler,
    ) {}

    public function catalogs(): JsonResponse
    {
        return ApiResponse::success(
            $this->getBeneficiosCatalogsHandler->handle(),
            'Catalogos de beneficios cargados'
        );
    }

    public function indexByEmpleado(int $empleadoId): JsonResponse
    {
        return ApiResponse::success(
            $this->getBeneficiariosByEmpleadoHandler->handle($empleadoId),
            'Beneficiarios cargados'
        );
    }

    public function store(CreateBeneficiarioRequest $request, int $empleadoId): JsonResponse
    {
        try {
            return ApiResponse::created(
                $this->createBeneficiarioHandler->handle($empleadoId, $request->validated()),
                'Beneficiario registrado correctamente'
            );
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        }
    }

    public function update(UpdateBeneficiarioRequest $request, int $id): JsonResponse
    {
        $record = $this->updateBeneficiarioHandler->handle($id, $request->validated());
        if (!$record) {
            return ApiResponse::notFound('Beneficiario no encontrado');
        }

        return ApiResponse::success($record, 'Beneficiario actualizado correctamente');
    }

    public function destroy(int $id): JsonResponse
    {
        if (!$this->deleteBeneficiarioHandler->handle($id)) {
            return ApiResponse::notFound('Beneficiario no encontrado');
        }

        return ApiResponse::success([], 'Beneficiario eliminado correctamente');
    }
}
