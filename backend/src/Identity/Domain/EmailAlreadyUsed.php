<?php

declare(strict_types=1);

namespace App\Identity\Domain;

use App\Shared\Domain\Conflict;

final class EmailAlreadyUsed extends Conflict
{
    public static function create(): self
    {
        // Message volontairement neutre : il ne confirme pas à un tiers qu'une adresse est inscrite.
        return new self('Impossible de créer ce compte avec cette adresse e-mail.');
    }
}
