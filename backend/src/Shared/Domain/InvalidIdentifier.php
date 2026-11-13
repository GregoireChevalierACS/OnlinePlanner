<?php

declare(strict_types=1);

namespace App\Shared\Domain;

final class InvalidIdentifier extends DomainException
{
    public static function empty(): self
    {
        return new self('Un identifiant ne peut pas être vide.');
    }
}
