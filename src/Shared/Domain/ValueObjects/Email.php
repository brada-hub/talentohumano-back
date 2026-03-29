<?php

namespace Src\Shared\Domain\ValueObjects;

use Exception;

class Email
{
    public function __construct(private readonly string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Formato de correo electrónico inválido.");
        }
    }

    public function value(): string { return $this->value; }
}
