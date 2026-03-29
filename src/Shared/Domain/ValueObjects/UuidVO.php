<?php

namespace Src\Shared\Domain\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

final class UuidVO
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $this->ensureIsValidUuid($value);
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Str::uuid()->toString());
    }

    public function value(): string
    {
        return $this->value;
    }

    private function ensureIsValidUuid(string $id): void
    {
        if (!Str::isUuid($id)) {
            throw new InvalidArgumentException(
                sprintf('<%s> does not allow the value <%s>.', static::class, $id)
            );
        }
    }
}
