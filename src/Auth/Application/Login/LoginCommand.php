<?php

namespace Src\Auth\Application\Login;

class LoginCommand
{
    public function __construct(
        public readonly string $username,
        public readonly string $password
    ) {}
}
