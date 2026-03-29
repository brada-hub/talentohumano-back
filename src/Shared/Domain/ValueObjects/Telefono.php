<?php

namespace Src\Shared\Domain\ValueObjects;

use Exception;

class Telefono
{
    public function __construct(private readonly string $value)
    {
        if (!preg_match('/^[67]\d{7}$/', $value)) {
            throw new Exception("El teléfono debe empezar con 6 o 7 y tener exactamente 8 dígitos.");
        }
    }

    public function value(): string { return $this->value; }
}
