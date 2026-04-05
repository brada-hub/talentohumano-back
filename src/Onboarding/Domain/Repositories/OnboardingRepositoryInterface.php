<?php

namespace Src\Onboarding\Domain\Repositories;

use Src\Onboarding\Domain\Entities\OnboardingToken;

interface OnboardingRepositoryInterface
{
    public function findByToken(string $token): ?OnboardingToken;
    public function saveToken(OnboardingToken $token): void;
    public function deactivateToken(string $token): void;
    public function isPortalEnabled(): bool;
    
    // Core onboarding data persistence
    public function saveFullOnboardingData(array $data, ?string $forcedPersonaId = null): void;
    public function findPersonaByCiAndBirthDate(string $ci, string $birthDate): ?array;
}
