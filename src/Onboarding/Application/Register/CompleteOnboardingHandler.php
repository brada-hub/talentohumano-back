<?php

namespace Src\Onboarding\Application\Register;

use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use InvalidArgumentException;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class CompleteOnboardingHandler
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $repo
    ) {}

    public function handle(array $data, string $token): void
    {
        // 1. Validar el portal (Seguridad: No permitir si está deshabilitado)
        if (!$this->repo->isPortalEnabled()) {
            throw new InvalidArgumentException("El portal de registro se encuentra deshabilitado temporalmente.");
        }

        // 2. Validar Token (si existe)
        if ($token && !str_starts_with($token, 'new_session_')) {
            $otoken = $this->repo->findByToken($token);
            if (!$otoken || !$otoken->canBeUsed()) {
                throw new InvalidArgumentException("Token de acceso inválido o expirado.");
            }
        }

        // 3. Ejecutar guardado persistente (Delegado al repo que maneja la transacción)
        $this->repo->saveFullOnboardingData($data);

        // 4. Desactivar token si fue usado
        if ($token && !str_starts_with($token, 'new_session_')) {
            $this->repo->deactivateToken($token);
        }
    }
}
