<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidProvider extends DomainException
{
    public static function emptyName(): self
    {
        return new self('Le nom du prestataire est obligatoire.');
    }

    public static function negativeNotice(int $minutes): self
    {
        return new self(\sprintf('Le délai de prévenance ne peut pas être négatif, %d reçu.', $minutes));
    }
}
