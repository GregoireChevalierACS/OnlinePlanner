<?php

declare(strict_types=1);

namespace App\Identity\Domain;

use App\Shared\Domain\DomainException;

final class InvalidLink extends DomainException
{
    public static function create(): self
    {
        // Un seul message pour « inconnu », « expiré » et « déjà utilisé » : rien à apprendre pour un attaquant.
        return new self('Ce lien est invalide ou a expiré. Demandez-en un nouveau.');
    }
}
