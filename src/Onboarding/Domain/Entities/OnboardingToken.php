<?php

namespace Src\Onboarding\Domain\Entities;

use DateTimeImmutable;

final class OnboardingToken
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $token,
        private readonly ?int $personaId,
        private readonly bool $activo,
        private readonly ?DateTimeImmutable $usadoEn
    ) {}

    public function id(): ?int { return $this->id; }
    public function token(): string { return $this->token; }
    public function personaId(): ?int { return $this->personaId; }
    public function isActivo(): bool { return $this->activo; }
    public function usadoEn(): ?DateTimeImmutable { return $this->usadoEn; }

    public function canBeUsed(): bool
    {
        return $this->activo && $this->usadoEn === null;
    }
}
