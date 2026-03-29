<?php

namespace Src\Shared\Domain\ValueObjects;

use Exception;

class CI
{
    public function __construct(private readonly string $value)
    {
        if (!preg_match('/^\d{5,10}$/', $value)) {
            throw new Exception("El CI debe tener entre 5 y 10 dígitos numéricos.");
        }
    }

    public function value(): string { return $this->value; }
}
