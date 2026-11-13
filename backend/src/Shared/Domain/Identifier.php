<?php

declare(strict_types=1);

namespace App\Shared\Domain;

/**
 * Value Object identifiant : la génération (UUID…) reste hors du domaine.
 */
abstract readonly class Identifier implements \Stringable
{
    final public function __construct(public string $value)
    {
        if ('' === trim($value)) {
            throw InvalidIdentifier::empty();
        }
    }

    public function equals(self $other): bool
    {
        return static::class === $other::class && $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
