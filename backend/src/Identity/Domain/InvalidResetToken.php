<?php

declare(strict_types=1);

namespace App\Identity\Domain;

use App\Shared\Domain\DomainException;

final class InvalidResetToken extends DomainException
{
    public static function create(): self
    {
        // Un seul message pour « inconnu », « expiré » et « déjà utilisé » : rien à apprendre pour un attaquant.
        return new self('Ce lien de réinitialisation est invalide ou a expiré. Demandez-en un nouveau.');
    }
}
