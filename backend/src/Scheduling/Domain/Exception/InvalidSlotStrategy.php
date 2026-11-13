<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidSlotStrategy extends DomainException
{
    public static function nonPositiveDuration(int $minutes): self
    {
        return new self(\sprintf('La durée d\'un rendez-vous doit être positive, %d reçu.', $minutes));
    }

    public static function negativeBuffer(int $minutes): self
    {
        return new self(\sprintf('Le temps de battement ne peut pas être négatif, %d reçu.', $minutes));
    }
}
