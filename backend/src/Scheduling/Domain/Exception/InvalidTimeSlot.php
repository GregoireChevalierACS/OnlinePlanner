<?php

declare(strict_types=1);

namespace App\Scheduling\Domain\Exception;

use App\Shared\Domain\DomainException;

final class InvalidTimeSlot extends DomainException
{
    public static function endNotAfterStart(): self
    {
        return new self('La fin du créneau doit être postérieure à son début.');
    }

    public static function nonPositiveDuration(int $minutes): self
    {
        return new self(\sprintf('La durée d\'un créneau doit être positive, %d reçu.', $minutes));
    }
}
