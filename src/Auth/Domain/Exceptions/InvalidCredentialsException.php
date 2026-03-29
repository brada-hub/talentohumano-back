<?php

namespace Src\Auth\Domain\Exceptions;

use Exception;

final class InvalidCredentialsException extends Exception
{
    public function __construct()
    {
        parent::__construct("Credenciales no válidas. Por favor, intente de nuevo.");
    }
}
