<?php

namespace Src\Onboarding\Application\Register;

use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use InvalidArgumentException;

final class CompleteOnboardingHandler
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $repo
    ) {}

    public function handle(array $data, string $token): void
    {
        $forcedPersonaId = null;

        if (!$this->repo->isPortalEnabled()) {
            throw new InvalidArgumentException('El portal de registro se encuentra deshabilitado temporalmente.');
        }

        if ($token && !str_starts_with($token, 'new_session_')) {
            $otoken = $this->repo->findByToken($token);
            if (!$otoken || !$otoken->canBeUsed()) {
                throw new InvalidArgumentException('Token de acceso invalido o expirado.');
            }

            $forcedPersonaId = $otoken->personaId();
        }

        $this->repo->saveFullOnboardingData($data, $forcedPersonaId);

        if ($token && !str_starts_with($token, 'new_session_')) {
            $this->repo->deactivateToken($token);
        }
    }
}
